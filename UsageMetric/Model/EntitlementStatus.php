<?php

declare(strict_types=1);

enum EntitlementStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case REVOKED = 'revoked';
    case EXPIRED = 'expired';
}