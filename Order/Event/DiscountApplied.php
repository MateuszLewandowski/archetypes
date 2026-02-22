<?php

declare(strict_types=1);

namespace Event;
use CollectionItem;
use CustomerId;
use Discount;
use PrivateEvent;
use PrivateEventId;

final readonly class DiscountApplied implements PrivateEvent
{
    public function __construct(
        public PrivateEventId $id,
        public Discount       $discount,
        public CustomerId     $customerId,
    ) {
    }

    public function identity(): string
    {
        return $this->id->value;
    }
}
