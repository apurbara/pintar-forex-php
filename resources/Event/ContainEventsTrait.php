<?php

namespace Resources\Event;

trait ContainEventsTrait
{

    /**
     * 
     * @var EventInterface[]
     */
    protected $recordedEvents = [];

    /**
     * 
     * @var ContainEventsInterface[]
     */
    protected $childrenContainEvents = [];

    /**
     * 
     * @return EventInterface[]
     */
    public function pullRecordedEvents(): array
    {
        $recordedEvents = $this->recordedEvents;
        $this->recordedEvents = [];
        
        $childrenEvents = [];
        foreach ($this->childrenContainEvents as $childContainEvents) {
            $childrenEvents[] = $childContainEvents->pullRecordedEvents();
        }
        
        return array_merge($recordedEvents, ...$childrenEvents);
    }

    public function recordEvent(EventInterface $event): void
    {
        $this->recordedEvents[] = $event;
    }

    protected function storeChildContainEvents(ContainEventsInterface $childContainEvents): void
    {
        $this->childrenContainEvents[] = $childContainEvents;
    }

}
