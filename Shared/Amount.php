<?php

declare(strict_types=1);

final readonly class Amount
{
    public function __construct(
        public float $value,
    ) {
    }

    public static function of(float $value): self
    {
        return new self($value);
    }

    public function subtract(self $amount): self
    {
        return new self($this->value - $amount->value);
    }

    public function isLessThanZero(): bool
    {
        return $this->value < .0;
    }

    public static function zero(): self
    {
        return new self(.0);
    }
}
