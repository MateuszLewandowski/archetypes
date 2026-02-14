<?php

declare(strict_types=1);

namespace Event;

use CollectionItem;
use OrderId;
use PrivateEvent;
use PrivateEventId;

final readonly class OrderCancelled implements PrivateEvent
{
    public PrivateEventId $id;

    public function __construct(
        public OrderId $orderId,
    ) {
        $this->id = new PrivateEventId('id');
    }

    public function equals(CollectionItem $item): bool
    {
        return $this->identity() === $item->identity();
    }

    public function identity(): string
    {
        return $this->id->value;
    }
}
