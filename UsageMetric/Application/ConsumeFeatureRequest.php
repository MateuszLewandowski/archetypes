
<?php

declare(strict_types=1);

namespace Application\DTO;

use Application\Command\ConsumeFeatureCommand;

final readonly class ConsumeFeatureRequest
{
    public function __construct(
        public string $customerId,
        public string $feature,
        public int $quantity,
        public ?string $reference = null,
    ) {
    }

    public function toCommand(): ConsumeFeatureCommand
    {
        return new ConsumeFeatureCommand(
            $this->customerId,
            $this->feature,
            $this->quantity,
            $this->reference,
            bin2hex(random_bytes(16)),  // Generate idempotency key
        );
    }
}