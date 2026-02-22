<?php

declare(strict_types=1);

final readonly class UsageConsumed implements PrivateEvent
{
    public function __construct(
        public EntitlementId $entitlementId,
        public string $customerId,
        public FeatureType $featureType,
        public Quantity $quantity,
        public UsageSource $source,
        public ?BucketId $bucketId,
        public ?string $reference,
        public DateTimeImmutable $consumedAt,
    ) {
    }
}