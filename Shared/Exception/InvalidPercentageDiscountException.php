<?php

declare(strict_types=1);

namespace Exception;

use InvalidArgumentException;

final class InvalidPercentageDiscountException extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('Percentage discount must be between 0 and 100');
    }
}
