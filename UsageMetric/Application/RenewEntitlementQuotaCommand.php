<?php

declare(strict_types=1);

namespace Application\Command;

final readonly class RenewEntitlementQuotaCommand
{
    public function __construct(
        public string $entitlementId,
        public ?string $renewalDateTime = null,
    ) {
    }
}