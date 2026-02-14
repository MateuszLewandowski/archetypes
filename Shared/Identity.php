<?php

declare(strict_types=1);

abstract readonly class Identity
{
    public function __construct(
        public string $value,
    ) {
    }
}
