<?php

declare(strict_types=1);

use Exception\InsufficientQuotaException;
use Exception\InvalidQuantityException;

final readonly class Bucket implements CollectionItem
{
    public function __construct(
        public BucketId $id,
        public Quantity $available,
        public Quantity $consumed,
        public DateTimeImmutable $expiresAt,
        public DateTimeImmutable $purchasedAt,
        public ?string $orderId = null,
        public BucketStatus $status = BucketStatus::ACTIVE,
    ) {
        if ($consumed->value > $available->value) {
            throw new InvalidQuantityException('Consumed cannot exceed available');
        }

        if ($consumed->value < 0 || $available->value < 0) {
            throw new InvalidQuantityException('Quantities cannot be negative');
        }
    }

    public static function create(
        Quantity $quantity,
        DateTimeImmutable $expiresAt,
        ?string $orderId = null,
    ): self {
        return new self(
            BucketId::create(),
            $quantity,
            new Quantity(0),
            $expiresAt,
            new DateTimeImmutable(),
            $orderId,
            BucketStatus::ACTIVE,
        );
    }

    public function identity(): string
    {
        return $this->id->value;
    }

    public function equals(CollectionItem $item): bool
    {
        return $item->identity() === $this->identity();
    }

    public function getRemaining(): int
    {
        return $this->available->value - $this->consumed->value;
    }

    public function canConsume(Quantity $quantity, DateTimeImmutable $now): bool
    {
        if ($this->status !== BucketStatus::ACTIVE) {
            return false;
        }

        if ($this->isExpired($now)) {
            return false;
        }

        return $quantity->value <= $this->getRemaining();
    }

    public function consume(Quantity $quantity, DateTimeImmutable $now): self
    {
        if (!$this->canConsume($quantity, $now)) {
            throw new InsufficientQuotaException(
                sprintf(
                    'Bucket cannot be consumed. Remaining: %d, Requested: %d',
                    $this->getRemaining(),
                    $quantity->value
                )
            );
        }

        return new self(
            $this->id,
            $this->available,
            new Quantity($this->consumed->value + $quantity->value),
            $this->expiresAt,
            $this->purchasedAt,
            $this->orderId,
            $this->getRemaining() === 0 ? BucketStatus::EXHAUSTED : BucketStatus::ACTIVE,
        );
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $now >= $this->expiresAt;
    }

    public function markAsExpired(): self
    {
        return new self(
            $this->id,
            $this->available,
            $this->consumed,
            $this->expiresAt,
            $this->purchasedAt,
            $this->orderId,
            BucketStatus::EXPIRED,
        );
    }

    public function getUtilizationPercentage(): float
    {
        if ($this->available->value === 0) {
            return 0.0;
        }

        return ($this->consumed->value / $this->available->value) * 100;
    }
}