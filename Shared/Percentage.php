<?php

declare(strict_types=1);

use Exception\InvalidPercentageDiscountException;

final readonly class Percentage
{
    public function __construct(
        public int $value,
    )
    {
        if ($this->value < 0 || $this->value > 100) {
            throw new InvalidPercentageDiscountException();
        }
    }
}
