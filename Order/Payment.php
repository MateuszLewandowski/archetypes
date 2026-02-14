<?php

declare(strict_types=1);

final readonly class Payment
{
    public function __construct(
        public OrderId $orderId,
        public Amount $amount,
        public PaymentStatus $status,
        public PaymentMethod $method,
        public DateTimeImmutable $processedAt,
    ) {
    }
}
