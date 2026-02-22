<?php

declare(strict_types=1);

namespace Event;

use RuntimeException;

final class OrderCannotBeModifiedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Order cannot be modified after it has been placed');
    }
}
