<?php

declare(strict_types=1);

final readonly class DiscountCode
{
    public function __construct(
        public string $value,
    ) {
    }
}
