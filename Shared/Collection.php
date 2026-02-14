<?php

declare(strict_types=1);

abstract class Collection
{
    public function __construct(
        /** @var CollectionItem[] */
        protected iterable $values = [],
    ) {
    }

    public function exists(CollectionItem $item): bool
    {
        foreach ($this->values as $value) {
            if ($value->equals($item)) {
                return true;
            }
        }

        return false;
    }

    public function add(CollectionItem $item): void
    {
        $this->values[] = $item;
    }
}
