<?php

namespace Resources\Event;

class EntityCreatedIdSpyListener implements ListenerInterface
{

    public readonly string $id;
    protected $spyFunction;

    public function __construct(callable $spyFunction)
    {
        $this->spyFunction = $spyFunction;
    }

    public function handle(EventInterface $event): void
    {
        $spyFunction = $this->spyFunction;
        $this->id = $spyFunction($event);
    }

}
