<?php

declare(strict_types=1);

final readonly class OrderItemType implements CollectionItem
{
    public function __construct(
        private string $value,
    ) {
    }

    public function equals(CollectionItem $item): bool
    {
        return $this->identity() === $item->identity();
    }

    public function identity(): string
    {
        return $this->value;
    }
}
