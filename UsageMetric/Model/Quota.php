<?php

declare(strict_types=1);

final readonly class Quota
{
    public function __construct(
        public Quantity $limit,
        public Quantity $consumed,
        public Period $period,
    ) {
        if ($consumed->value > $limit->value) {
            throw new InvalidQuantityException('Consumed cannot exceed limit');
        }

        if ($consumed->value < 0 || $limit->value < 0) {
            throw new InvalidQuantityException('Quantities cannot be negative');
        }
    }

    public static function create(Quantity $limit, Period $period = Period::MONTHLY): self
    {
        return new self($limit, new Quantity(0), $period);
    }

    public function getAvailable(): int
    {
        return $this->limit->value - $this->consumed->value;
    }

    public function canConsume(Quantity $quantity): bool
    {
        return $quantity->value <= $this->getAvailable();
    }

    public function consume(Quantity $quantity): self
    {
        if (!$this->canConsume($quantity)) {
            throw new InsufficientQuotaException(
                sprintf(
                    'Insufficient quota. Available: %d, Requested: %d',
                    $this->getAvailable(),
                    $quantity->value
                )
            );
        }

        return new self(
            $this->limit,
            new Quantity($this->consumed->value + $quantity->value),
            $this->period,
        );
    }

    public function reset(): self
    {
        return new self($this->limit, new Quantity(0), $this->period);
    }

    public function getUtilizationPercentage(): float
    {
        if ($this->limit->value === 0) {
            return 0.0;
        }

        return ($this->consumed->value / $this->limit->value) * 100;
    }

    public function isNearLimit(Percentage $threshold = null): bool
    {
        $threshold ??= new Percentage(80);

        return $this->getUtilizationPercentage() >= $threshold->value;
    }
}