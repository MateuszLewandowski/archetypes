<?php

declare(strict_types=1);

namespace Exception;

use Error;

final class IncompatibleCurrencyTypesError extends Error
{
    public function __construct()
    {
        parent::__construct('Incompatible currency types');
    }
}
