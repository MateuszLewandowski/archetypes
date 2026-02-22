<?php

declare(strict_types=1);

final readonly class FeatureName
{
    public function __construct(
        public string $value,
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('Feature name cannot be empty');
        }
    }

    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }
}