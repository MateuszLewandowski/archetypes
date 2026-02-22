<?php

declare(strict_types=1);

final readonly class UsageRefused implements PrivateEvent
{
    public function __construct(
        public EntitlementId $entitlementId,
        public string $customerId,
        public FeatureType $featureType,
        public Quantity $quantity,
        public string $reason,
        public DateTimeImmutable $refusedAt,
    ) {
    }
}