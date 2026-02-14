<?php

declare(strict_types=1);

final readonly class OrderItem implements CollectionItem
{
    public function __construct(
        public OrderItemId $id,
        public OrderItemType $type,
        public Money $money,
        public Tax $tax,
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
}
