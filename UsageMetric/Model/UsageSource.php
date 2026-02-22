<?php

declare(strict_types=1);

enum UsageSource: string
{
    case PLAN_QUOTA = 'plan_quota';
    case BUCKET = 'bucket';
    case MANUAL_ADJUSTMENT = 'manual_adjustment';
}