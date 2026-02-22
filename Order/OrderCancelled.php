<?php

declare(strict_types=1);

final readonly class OrderCancelled implements PrivateEvent
{
    public function __construct(
        public OrderId $orderId,
        public DateTimeImmutable $cancelledAt,
    ) {
    }

    public function identity(): string
    {
        return $this->orderId->value;
    }
}
