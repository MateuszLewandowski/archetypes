<?php

declare(strict_types=1);

final readonly class Money
{
    public function __construct(
        public Amount $amount,
        public Currency $currency,
    ) {
    }
}
