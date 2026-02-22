<?php

declare(strict_types=1);

use Exception\IncompatibleCurrencyTypesError;

final readonly class Money
{
    public function __construct(
        public Amount $amount,
        public Currency $currency,
    ) {
    }

    public function subtract(self $money): self
    {
        if (!$this->hasTheSameCurrency($money)) {
            throw new IncompatibleCurrencyTypesError();
        }

        $value = $this->amount->subtract($money->amount);

        return new self(
            $value->isLessThanZero() ? Amount::zero() : $value,
            $this->currency,
        );
    }

    private function hasTheSameCurrency(self $money): bool
    {
        return $this->currency->isTheSame($money->currency);
    }
}
