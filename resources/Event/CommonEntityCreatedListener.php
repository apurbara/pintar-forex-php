<?php

namespace Resources\Event;

class CommonEntityCreatedListener implements ListenerInterface
{
    public $createdEntityId;

    public function handle(EventInterface $event): void
    {
        $this->execute($event);
    }
    
    protected function execute(CommonEvent $event): void
    {
        $this->createdEntityId = $event->getId();
    }

}
