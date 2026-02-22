<?php

declare(strict_types=1);

use Event\OrderAlreadyCancelledException;
use Event\OrderCannotBeModifiedException;
use Exception\EmptyOrderException;

final class Order extends AggregateRoot
{
    public readonly DateTimeImmutable $createdAt;
    private OrderStatus $status;
    private OrderItems $orderItems;
    private Discounts $discounts;
    private ?Payment $payment = null;
    private ?Tax $tax = null;

    public function __construct(
        public readonly OrderId $id,
        OrderItems $orderItems,
        private CustomerId $customerId,
    ) {
        if ($orderItems->isEmpty()) {
            throw new EmptyOrderException();
        }

        $this->createdAt = new DateTimeImmutable();
        $this->status = OrderStatus::PENDING;
        $this->orderItems = $orderItems;
        $this->discounts = new Discounts();

        parent::__construct();
    }

    /** @throws EmptyOrderException */
    public static function create(
        OrderItems $orderItems,
        CustomerId $customerId,
    ): self {
        return new self(
            OrderId::create(),
            $orderItems,
            $customerId,
        );
    }

    public function addItem(OrderItem $item): void
    {
        $this->assertOrderCanBeModified();
        $this->orderItems->add($item);
        $this->calculateTotal();
    }

    public function applyDiscount(Discount $discount): void
    {
        $this->assertOrderCanBeModified();
        $this->discounts->add($discount);
        $this->calculateTotal();
    }

    public function cancel(): void
    {
        if ($this->status === OrderStatus::CANCELLED) {
            throw new OrderAlreadyCancelledException();
        }

        $this->status = OrderStatus::CANCELLED;
        $this->pushEvent(new OrderCancelled($this->id, new DateTimeImmutable()));
    }

    private function assertOrderCanBeModified(): void
    {
        if ($this->status !== OrderStatus::PENDING) {
            throw new OrderCannotBeModifiedException();
        }
    }

    private function calculateTotal(): void
    {
        // get items, get discounts, use tax, calculate total
    }
}