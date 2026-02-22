<?php

declare(strict_types=1);

final readonly class CanConsumeSpecification
{
    public function __construct(
        private Entitlement $entitlement,
        private Quantity $quantity,
    ) {
    }

    public function isSatisfiedBy(): bool
    {
        $now = new DateTimeImmutable();

        if ($this->entitlement->getStatus() !== EntitlementStatus::ACTIVE) {
            return false;
        }

        if ($this->entitlement->getPlanQuota()->canConsume($this->quantity)) {
            return true;
        }

        return $this->entitlement->getBuckets()->getTotalRemaining($now) >= $this->quantity->value;
    }

    public function getReason(): string
    {
        if ($this->entitlement->getStatus() !== EntitlementStatus::ACTIVE) {
            return sprintf('Entitlement is %s', $this->entitlement->getStatus()->value);
        }

        return sprintf(
            'Insufficient quota: %d requested, %d available total',
            $this->quantity->value,
            $this->entitlement->getPlanQuota()->getAvailable() +
            $this->entitlement->getBuckets()->getTotalRemaining(new DateTimeImmutable())
        );
    }
}