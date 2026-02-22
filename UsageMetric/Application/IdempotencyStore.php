<?php

declare(strict_types=1);

namespace Application\Service;

interface IdempotencyStore
{
    public function get(string $key): mixed;

    public function store(string $key, mixed $value, int $ttlSeconds): void;

    public function has(string $key): bool;

    public function forget(string $key): void;
}