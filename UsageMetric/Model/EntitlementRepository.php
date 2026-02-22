<?php

declare(strict_types=1);

interface EntitlementRepository
{
    public function findById(EntitlementId $id): Entitlement;

    public function findByCustomerAndFeature(string $customerId, FeatureType $featureType): Entitlement;

    public function findBySubscription(string $subscriptionId): array;

    public function save(Entitlement $entitlement): void;
}
