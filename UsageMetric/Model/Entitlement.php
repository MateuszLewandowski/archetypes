<?php

declare(strict_types=1);

use Exception\EntitlementNotActiveException;
use Exception\InsufficientQuotaException;
use Exception\InvalidBucketExpirationException;

final class Entitlement extends AggregateRoot
{
    public readonly DateTimeImmutable $grantedAt;
    private EntitlementStatus $status;
    private RenewalCycle $renewalCycle;

    public function __construct(
        public readonly EntitlementId $id,
        public readonly string $customerId,
        public readonly string $subscriptionId,
        public readonly FeatureType $featureType,
        private Quota $planQuota,
        private Buckets $buckets,
        private UsageLog $usageLog,
        private DepletionStrategy $depletionStrategy,
        RenewalCycle $renewalCycle,
        EntitlementStatus $status = EntitlementStatus::ACTIVE,
    ) {
        $this->grantedAt = new DateTimeImmutable();
        $this->renewalCycle = $renewalCycle;
        $this->status = $status;

        parent::__construct();
    }

    public static function grantFromPlan(
        string $customerId,
        string $subscriptionId,
        FeatureType $featureType,
        Quota $quota,
        Period $period = Period::MONTHLY,
    ): self {
        $renewalCycle = match ($period) {
            Period::MONTHLY => RenewalCycle::monthly(new DateTimeImmutable()),
            Period::YEARLY => RenewalCycle::yearly(new DateTimeImmutable()),
        };

        $entitlement = new self(
            EntitlementId::create(),
            $customerId,
            $subscriptionId,
            $featureType,
            $quota,
            new Buckets(),
            new UsageLog(),
            DepletionStrategy::EXPIRY_FIRST,
            $renewalCycle,
        );

        $entitlement->pushEvent(new EntitlementGranted(
            $entitlement->id,
            $customerId,
            $subscriptionId,
            $featureType,
            $quota,
            new DateTimeImmutable(),
        ));

        return $entitlement;
    }

    public function addBucket(
        Quantity $quantity,
        DateTimeImmutable $expiresAt,
        ?string $orderId = null,
    ): BucketId {
        $this->assertActive();

        if ($expiresAt <= new DateTimeImmutable()) {
            throw new InvalidBucketExpirationException('Bucket expiration must be in the future');
        }

        $bucket = Bucket::create($quantity, $expiresAt, $orderId);
        $this->buckets->add($bucket);

        $this->pushEvent(new BucketAdded(
            $this->id,
            $this->customerId,
            $this->featureType,
            $bucket->id,
            $quantity,
            $expiresAt,
            new DateTimeImmutable(),
        ));

        return $bucket->id;
    }

    public function consume(
        Quantity $quantity,
        ?string $reference = null,
    ): UsageResult {
        $this->assertActive();

        $now = new DateTimeImmutable();

        $this->cleanExpiredBuckets($now);

        $usage = $this->tryConsumeFromBucket($quantity, $reference, $now);

        if ($usage !== null) {
            $this->usageLog->add($usage);

            $this->pushEvent(new UsageConsumed(
                $this->id,
                $this->customerId,
                $this->featureType,
                $quantity,
                UsageSource::BUCKET,
                $usage->bucketId,
                $reference,
                new DateTimeImmutable(),
            ));

            return UsageResult::allowed(
                $usage,
                $this->planQuota->getAvailable(),
                $this->buckets->getTotalRemaining($now),
                'bucket',
            );
        }

        if ($this->planQuota->canConsume($quantity)) {
            $this->planQuota = $this->planQuota->consume($quantity);
            $usage = Usage::fromPlanQuota($this->featureType, $quantity, $reference);
            $this->usageLog->add($usage);

            $this->pushEvent(new UsageConsumed(
                $this->id,
                $this->customerId,
                $this->featureType,
                $quantity,
                UsageSource::PLAN_QUOTA,
                null,
                $reference,
                new DateTimeImmutable(),
            ));

            return UsageResult::allowed(
                $usage,
                $this->planQuota->getAvailable(),
                $this->buckets->getTotalRemaining($now),
                'plan_quota',
            );
        }

        $usage = Usage::refused($this->featureType, $quantity, 'Insufficient quota');
        $this->usageLog->add($usage);

        $this->pushEvent(new UsageRefused(
            $this->id,
            $this->customerId,
            $this->featureType,
            $quantity,
            'Insufficient quota',
            new DateTimeImmutable(),
        ));

        return UsageResult::refused(
            $usage,
            $this->planQuota->getAvailable(),
            $this->buckets->getTotalRemaining($now),
        );
    }

    /**
     * Reset quota (called on renewal date)
     */
    public function renewQuota(DateTimeImmutable $now): void
    {
        $this->assertActive();

        if (!$this->renewalCycle->isExpired($now)) {
            return;
        }

        $this->planQuota = $this->planQuota->reset();
        $this->renewalCycle = match ($this->planQuota->period) {
            Period::MONTHLY => RenewalCycle::monthly($now),
            Period::YEARLY => RenewalCycle::yearly($now),
        };

        $this->pushEvent(new QuotaReset(
            $this->id,
            $this->customerId,
            $this->featureType,
            $this->planQuota,
            new DateTimeImmutable(),
        ));
    }

    /**
     * Revoke entitlement entirely
     */
    public function revoke(string $reason = 'Unknown'): void
    {
        $this->assertActive();

        $this->status = EntitlementStatus::REVOKED;

        $this->pushEvent(new EntitlementRevoked(
            $this->id,
            $this->customerId,
            $this->featureType,
            $reason,
            new DateTimeImmutable(),
        ));
    }

    /**
     * Suspend entitlement temporarily
     */
    public function suspend(): void
    {
        if ($this->status !== EntitlementStatus::ACTIVE) {
            throw new EntitlementNotActiveException('Cannot suspend inactive entitlement');
        }

        $this->status = EntitlementStatus::SUSPENDED;
    }

    /**
     * Resume suspended entitlement
     */
    public function resume(): void
    {
        if ($this->status !== EntitlementStatus::SUSPENDED) {
            throw new EntitlementNotActiveException('Entitlement is not suspended');
        }

        $this->status = EntitlementStatus::ACTIVE;
    }

    public function getState(): array
    {
        $now = new DateTimeImmutable();

        return [
            'id' => $this->id->value,
            'customerId' => $this->customerId,
            'subscriptionId' => $this->subscriptionId,
            'feature' => $this->featureType->value,
            'status' => $this->status->value,
            'planQuota' => [
                'limit' => $this->planQuota->limit->value,
                'consumed' => $this->planQuota->consumed->value,
                'available' => $this->planQuota->getAvailable(),
                'period' => $this->planQuota->period->value,
                'utilizationPercentage' => $this->planQuota->getUtilizationPercentage(),
            ],
            'buckets' => [
                'total' => $this->buckets->count(),
                'active' => $this->buckets->findActive($now)->count(),
                'totalRemaining' => $this->buckets->getTotalRemaining($now),
                'totalAvailable' => $this->buckets->getTotalAvailable(),
            ],
            'usage' => [
                'totalConsumed' => $this->usageLog->getTotalConsumedForFeature($this->featureType),
                'totalRefused' => $this->usageLog->getRefused()->count(),
                'totalMetrics' => $this->usageLog->count(),
            ],
            'renewal' => [
                'nextRenewalAt' => $this->renewalCycle->getNextRenewalAt()->format('c'),
                'period' => $this->renewalCycle->period->value,
            ],
            'grantedAt' => $this->grantedAt->format('c'),
        ];
    }

    private function tryConsumeFromBucket(
        Quantity $quantity,
        ?string $reference,
        DateTimeImmutable $now,
    ): ?Usage {
        $bucket = $this->buckets->findBucketForConsumption($now);

        if ($bucket === null) {
            return null;
        }

        try {
            $consumedBucket = $bucket->consume($quantity, $now);
            $this->buckets->remove($bucket);
            $this->buckets->add($consumedBucket);

            return Usage::fromBucket($this->featureType, $quantity, $bucket->id, $reference);
        } catch (InsufficientQuotaException) {
            return null;
        }
    }

    private function cleanExpiredBuckets(DateTimeImmutable $now): void
    {
        $expired = $this->buckets->findExpired($now);

        foreach ($expired->list() as $bucket) {
            if ($bucket instanceof Bucket) {
                $expiredBucket = $bucket->markAsExpired();
                $this->buckets->remove($bucket);
                $this->buckets->add($expiredBucket);

                $this->pushEvent(new BucketExpired(
                    $this->id,
                    $this->customerId,
                    $this->featureType,
                    $bucket->id,
                    $bucket->getRemaining(),
                    new DateTimeImmutable(),
                ));
            }
        }
    }

    private function assertActive(): void
    {
        if ($this->status !== EntitlementStatus::ACTIVE) {
            throw new EntitlementNotActiveException(
                sprintf('Entitlement is %s', $this->status->value)
            );
        }
    }

    public function getStatus(): EntitlementStatus
    {
        return $this->status;
    }

    public function getPlanQuota(): Quota
    {
        return $this->planQuota;
    }

    public function getBuckets(): Buckets
    {
        return $this->buckets;
    }
}