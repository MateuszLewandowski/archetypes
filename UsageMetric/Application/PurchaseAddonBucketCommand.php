<?php

declare(strict_types=1);

namespace Application\Command;

final readonly class PurchaseAddonBucketCommand
{
    public function __construct(
        public string $customerId,
        public string $subscriptionId,
        public string $feature,
        public int $quantity,
        public string $expirationDate,
        public ?string $orderId = null,
        public ?string $idempotencyKey = null,
    ) {
    }
}