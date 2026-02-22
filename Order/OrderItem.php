<?php

declare(strict_types=1);

final readonly class OrderItem implements CollectionItem
{
    public function __construct(
        public OrderItemId $id,
        public OrderItemType $type,
        public Money $money,
        public Tax $tax,
        public Quantity $quantity,
    ) {
    }

    public function equals(CollectionItem $item): bool
    {
        return $item->identity() === $this->id->value;
    }

    public function identity(): string
    {
        return $this->id->value;
    }

    public function getTotal(): Money
    {
        return new Money(
            Amount::of($this->money->amount->value * $this->quantity->value),
            $this->money->currency,
        );
    }


    public function isEmpty(): bool
    {
        return $this->quantity->value === 0;
    }
}
