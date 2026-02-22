<?php

declare(strict_types=1);

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';


    public function isFinal(): bool
    {
        return in_array($this, [
            self::DELIVERED,
            self::CANCELLED,
            self::REFUNDED,
        ], true);
    }

    public function isModifiable(): bool
    {
        return !$this->isFinal();
    }

}
