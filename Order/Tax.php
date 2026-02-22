<?php

declare(strict_types=1);

final readonly class Tax
{
    public function __construct(
        public Money $money,
        public Percentage $rate,
    ) {
    }

    public function calculate(Money $money): Money
    {
        $taxAmount = $money->amount->value * ($this->rate->value / 100);

        return new Money(
            Amount::of($taxAmount),
            $money->currency,
        );
    }

}
