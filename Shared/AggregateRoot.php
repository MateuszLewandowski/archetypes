<?php

declare(strict_types=1);

abstract class AggregateRoot
{
    private PrivateEvents $events;

    public function __construct()
    {
        $this->events = new PrivateEvents();
    }

    public function pushEvent(PrivateEvent $event): void
    {
        $this->events->add($event);
    }

    public function pullEvents(): PrivateEvents
    {
        $events = $this->events;
        $this->events = new PrivateEvents();

        return $events;
    }
}
