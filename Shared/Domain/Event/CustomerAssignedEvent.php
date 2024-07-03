<?php

namespace Shared\Domain\Event;

use Resources\Event\AbstractEvent;

readonly class CustomerAssignedEvent extends AbstractEvent
{

    public function __construct(public string $customerAssignmentId)
    {
        
    }
}
