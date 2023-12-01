<?php

namespace SharedContext\Domain\Event;

use Resources\Event\AbstractEvent;

readonly class InHouseSalesCustomerAssignmentRecycledEvent extends AbstractEvent
{
    
    public function __construct(public string $customerId)
    {
    }
    
}
