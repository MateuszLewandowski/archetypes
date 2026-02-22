<?php

declare(strict_types=1);

final readonly class FixedDiscount extends Discount

{
    public function __construct(
        DiscountCode $code,
        DiscountType $type,
        OrderItemTypes $applicableFor,
        public Money $money,
    ) {
        parent::__construct($code, $type, $applicableFor);
    }

    public function equals(CollectionItem $item): bool
    {
        return $this->identity() === $item->identity();
    }

    public function identity(): string
    {
        return $this->code->value;
    }

    public function calculate(Money $money): Money
    {
        return $this->money->subtract($money);
    }

}
