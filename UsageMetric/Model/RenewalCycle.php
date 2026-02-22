<?php

declare(strict_types=1);

final readonly class RenewalCycle
{
    public function __construct(
        public Period $period,
        public DateTimeImmutable $nextRenewalAt,
    ) {
    }

    public static function monthly(DateTimeImmutable $startDate): self
    {
        $nextRenewal = $startDate->modify('+1 month');

        return new self(
            Period::MONTHLY,
            $nextRenewal,
        );
    }

    public static function yearly(DateTimeImmutable $startDate): self
    {
        $nextRenewal = $startDate->modify('+1 year');

        return new self(
            Period::YEARLY,
            $nextRenewal,
        );
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $now >= $this->nextRenewalAt;
    }

    public function getNextRenewalAt(): DateTimeImmutable
    {
        return $this->nextRenewalAt;
    }
}