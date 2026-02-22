<?php

declare(strict_types=1);

enum BucketStatus: string
{
    case ACTIVE = 'active';
    case EXHAUSTED = 'exhausted';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';
}