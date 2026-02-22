<?php

declare(strict_types=1);

abstract readonly class Discount implements CollectionItem
{
    public function __construct(
        public DiscountCode $code,
        public DiscountType $type,
        public OrderItemTypes $applicableFor,
    ) {
    }

    public function isApplicableFor(OrderItemType $orderItemType): bool
    {
        return $this->applicableFor->exists($orderItemType);
    }

    abstract public function calculate(Money $money): Money;
}
