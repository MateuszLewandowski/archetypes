<?php

declare(strict_types=1);

enum DiscountType: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';
}
