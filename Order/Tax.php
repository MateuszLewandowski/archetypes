<?php

declare(strict_types=1);

final readonly class Tax
{
    public function __construct(
        public Amount $rate,
        public Amount $value,
    ) {
    }
}
