<?php

declare(strict_types=1);

final readonly class UsageResult
{
    public function __construct(
        public bool $allowed,
        public Usage $usage,
        public int $remainingInQuota,
        public int $remainingInBuckets,
        public ?string $consumedFromSource = null,
    ) {
    }

    public static function allowed(
        Usage $usage,
        int $remainingInQuota,
        int $remainingInBuckets,
        string $source,
    ): self {
        return new self(
            true,
            $usage,
            $remainingInQuota,
            $remainingInBuckets,
            $source,
        );
    }

    public static function refused(
        Usage $usage,
        int $remainingInQuota,
        int $remainingInBuckets,
    ): self {
        return new self(
            false,
            $usage,
            $remainingInQuota,
            $remainingInBuckets,
            null,
        );
    }

    public function getRemaining(): int
    {
        return $this->remainingInQuota + $this->remainingInBuckets;
    }
}