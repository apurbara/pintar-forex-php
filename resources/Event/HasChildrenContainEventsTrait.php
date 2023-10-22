<?php

namespace Resources\Event;

trait HasChildrenContainEventsTrait
{

    /**
     * 
     * @var ContainEventsInterface
     */
    protected $childrenContainEvents = [];

    public function pullRecordedEvents(): array
    {
        $childrenEvents = [];
        foreach ($this->childrenContainEvents as $childContainEvents) {
            $childrenEvents[] = $childContainEvents->pullRecordedEvents();
        }
        return array_merge(...$childEvents);
    }

    protected function storeChildContainEvents(ContainEventsInterface $childContainEvents): void
    {
        $this->childrenContainEvents[] = $childContainEvents;
    }

}
