<?php

namespace Sales\Domain\Event;

use Resources\Event\AbstractEvent;

readonly class CustomerValidated extends AbstractEvent
{

    public function __construct(public string $greetingAssignmentId)
    {
        
    }
}
