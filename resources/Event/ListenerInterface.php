<?php

namespace Resources\Event;

interface ListenerInterface
{

    public function handle(EventInterface $event): void;
}
