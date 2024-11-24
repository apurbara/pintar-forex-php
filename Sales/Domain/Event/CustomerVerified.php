<?php

namespace Sales\Domain\Event;

use Resources\Event\AbstractEvent;

readonly class CustomerVerified extends AbstractEvent
{

    public function __construct(public string $factFindingAssignmentId)
    {
        
    }
}
