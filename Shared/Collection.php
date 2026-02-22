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
        if ($this->exists($item)) {
            return;
        }

        $this->values[] = $item;
    }

    public function remove(CollectionItem $item): void
    {
        foreach ($this->values as $value) {
            if ($value->equals($item)) {
                $this->values = array_filter(
                    $this->values,
                    static fn (CollectionItem $i) => !$i->equals($item)
                );
                return;
            }
        }
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function list(): array
    {
        return $this->values;
    }

    public function isEmpty(): bool
    {
        return empty($this->values);
    }
}
