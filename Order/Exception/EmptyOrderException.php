<?php

declare(strict_types=1);

namespace Exception;

use Exception;

final class EmptyOrderException extends Exception
{
    public function __construct()
    {
        parent::__construct('Order must have at least one item');
    }
}
