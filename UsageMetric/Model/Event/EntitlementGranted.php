<?php

declare(strict_types=1);

final readonly class EntitlementGranted implements PrivateEvent
{
    public function __construct(
        public EntitlementId $entitlementId,
        public string $customerId,
        public string $subscriptionId,
        public FeatureType $featureType,
        public Quota $quota,
        public DateTimeImmutable $grantedAt,
    ) {
    }
}