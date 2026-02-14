<?php

declare(strict_types=1);

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';
}
