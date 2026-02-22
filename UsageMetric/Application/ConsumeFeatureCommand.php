<?php

declare(strict_types=1);

namespace Application\Command;

final readonly class ConsumeFeatureCommand
{
    public function __construct(
        public string $customerId,
        public string $feature,
        public int $quantity,
        public ?string $reference = null,
        public ?string $idempotencyKey = null,
    ) {
    }
}