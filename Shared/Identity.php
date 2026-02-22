<?php

declare(strict_types=1);

abstract readonly class Identity
{
    public function __construct(
        public string $value,
    ) {
    }

    public static function create(): static
    {
        return new static('uuid-v7');
    }
}
