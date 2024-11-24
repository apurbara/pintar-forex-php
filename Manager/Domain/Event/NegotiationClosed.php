<?php

namespace Manager\Domain\Event;

use Resources\Event\AbstractEvent;

readonly class NegotiationClosed extends AbstractEvent
{

    public function __construct(public string $strikingAssignmentId)
    {
        
    }
}
