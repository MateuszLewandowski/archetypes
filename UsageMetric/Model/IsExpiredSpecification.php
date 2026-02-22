<?php

declare(strict_types=1);

final readonly class IsExpiredSpecification
{
    public function __construct(
        private Bucket $bucket,
        private DateTimeImmutable $now,
    ) {
    }

    public function isSatisfiedBy(): bool
    {
        return $this->bucket->isExpired($this->now);
    }

    public function getExpiresIn(): int
    {
        $seconds = $this->bucket->expiresAt->getTimestamp() - $this->now->getTimestamp();

        return max(0, $seconds);
    }
}