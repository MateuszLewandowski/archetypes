<?php

declare(strict_types=1);

final readonly class BucketAdded implements PrivateEvent
{
    public function __construct(
        public EntitlementId $entitlementId,
        public string $customerId,
        public FeatureType $featureType,
        public BucketId $bucketId,
        public Quantity $quantity,
        public DateTimeImmutable $expiresAt,
        public DateTimeImmutable $addedAt,
    ) {
    }
}