<?php

declare(strict_types=1);

final readonly class QuotaReset implements PrivateEvent
{
    public function __construct(
        public EntitlementId $entitlementId,
        public string $customerId,
        public FeatureType $featureType,
        public Quota $newQuota,
        public DateTimeImmutable $resetAt,
    ) {
    }
}