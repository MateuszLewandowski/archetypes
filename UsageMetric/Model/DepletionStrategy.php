<?php

declare(strict_types=1);

enum DepletionStrategy: string
{
    /**
     * Use buckets expiring soonest first, then plan quota
     * Best for perishable resources (SMS, API calls)
     */
    case EXPIRY_FIRST = 'expiry_first';

    /**
     * Use oldest buckets first, then plan quota
     * Best for fair allocation
     */
    case FIFO = 'fifo';

    /**
     * Use newest buckets first, then plan quota
     * Incentivizes recent purchases
     */
    case LIFO = 'lifo';

    /**
     * Use plan quota first, then buckets
     * Best for maximizing plan usage
     */
    case PLAN_FIRST = 'plan_first';
}