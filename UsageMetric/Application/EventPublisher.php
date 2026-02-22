<?php

declare(strict_types=1);

namespace Application\Service;

use PrivateEvent;

interface EventPublisher
{
    public function publish(PrivateEvent $event): void;

    public function publishBatch(array $events): void;
}