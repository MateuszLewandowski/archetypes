<?php

declare(strict_types=1);

namespace Application\DTO;

use Application\Command\PurchaseAddonBucketCommand;

final readonly class PurchaseAddonBucketRequest
{
    public function __construct(
        public string $customerId,
        public string $subscriptionId,
        public string $feature,
        public int $quantity,
        public string $expirationDate,
        public ?string $orderId = null,
    ) {
    }

    public function toCommand(): PurchaseAddonBucketCommand
    {
        return new PurchaseAddonBucketCommand(
            $this->customerId,
            $this->subscriptionId,
            $this->feature,
            $this->quantity,
            $this->expirationDate,
            $this->orderId,
            bin2hex(random_bytes(16)),
        );
    }
}