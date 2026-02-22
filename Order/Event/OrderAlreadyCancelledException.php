<?php

declare(strict_types=1);

namespace Event;

use RuntimeException;

final class OrderAlreadyCancelledException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Order already cancelled');
    }
}
