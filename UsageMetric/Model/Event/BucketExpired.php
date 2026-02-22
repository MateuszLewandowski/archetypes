<?php

declare(strict_types=1);

final readonly class BucketExpired implements PrivateEvent
{
    public function __construct(
        public EntitlementId $entitlementId,
        public string $customerId,
        public FeatureType $featureType,
        public BucketId $bucketId,
        public int $remainingQuantity,
        public DateTimeImmutable $expiredAt,
    ) {
    }
}