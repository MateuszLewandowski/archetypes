<?php

declare(strict_types=1);

final readonly class PercentageDiscount extends Discount
{
    public function __construct(
        DiscountCode $code,
        DiscountType $type,
        OrderItemTypes $applicableFor,
        public Percentage $percentage,

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
        $discountedAmount = $money->amount->value * ($this->percentage->value / 100);

        return new Money(
            Amount::of($money->amount->value - $discountedAmount),
            $money->currency,
        );
    }

}
