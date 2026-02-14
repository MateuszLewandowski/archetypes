<?php

declare(strict_types=1);

interface CollectionItem
{
    public function equals(self $item): bool;

    public function identity(): mixed;
}
