<?php

declare(strict_types=1);

final readonly class Payment
{
    public function __construct(
        public Money $amount,
        public PaymentMethod $method,
        public PaymentStatus $status,
        public DateTimeImmutable $paidAt,

    ) {
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

}
