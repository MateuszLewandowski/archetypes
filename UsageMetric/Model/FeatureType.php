<?php

declare(strict_types=1);

enum FeatureType: string
{
    case SMS = 'sms';

    public function getUnit(): string
    {
        return match ($this) {
            self::SMS => 'message',
        };
    }
}