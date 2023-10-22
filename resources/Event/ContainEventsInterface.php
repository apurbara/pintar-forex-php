<?php

namespace Resources\Event;

interface ContainEventsInterface
{

    /**
     * 
     * @return EventInterface[]
     */
    public function pullRecordedEvents(): array;
}
