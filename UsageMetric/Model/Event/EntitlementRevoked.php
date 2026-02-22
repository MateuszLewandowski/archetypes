<?php

declare(strict_types=1);

final readonly class EntitlementRevoked implements PrivateEvent
{
    public function __construct(
        public EntitlementId $entitlementId,
        public string $customerId,
        public FeatureType $featureType,
        public string $reason,
        public DateTimeImmutable $revokedAt,
    ) {
    }
}