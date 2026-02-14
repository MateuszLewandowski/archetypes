<?php

declare(strict_types=1);

use Exception\UnsupportedDiscountError;

final class Order extends AggregateRoot
{
    public readonly DateTimeImmutable $createdAt;

    public function __construct(
        public readonly OrderId $id,
        public readonly OrderNumber $orderNumber,
        public OrderStatus $status,
        public OrderItems $orderItems,
        public CustomerId $customerId,
        public ?Payment $payment = null,
        public ?Discounts $discounts = null,
        public ?Tax $tax = null,
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->status = OrderStatus::PENDING;

        parent::__construct();
    }

    public function addItem(OrderItem $item): void
    {
        $this->orderItems->add($item);
        $this->calculateTotal();
    }

    public function applyDiscount(Discount $discount): void
    {
        if ($this->discounts === null) {
            throw new UnsupportedDiscountError('e');
        }

        $this->discounts->add($discount);
        $this->calculateTotal();
    }

    private function calculateTotal(): void
    {
        // get items, get discounts, use tax, calculate total
    }

    public function cancel(): void
    {
        $this->status = OrderStatus::CANCELLED;
        $this->pushEvent(new \Event\OrderCancelled(
            $this->id,
        ));
    }
}
