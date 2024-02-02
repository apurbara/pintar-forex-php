<?php

namespace SharedContext\Domain\Event;

use Resources\Event\EventInterface;

class MultipleCustomerAssignmentReceivedBySales implements EventInterface
{

    const NAME = "MultipleCustomerAssignmentReceivedBySales";
    protected array $assignedCustomerIdList = [];
    
    public function __construct(public readonly string $salesId)
    {
    }

    public function getAssignedCustomerIdList(): array
    {
        return $this->assignedCustomerIdList;
    }

    //
    public function addAssignedCustomerIdList(string $assignedCustomerId)
    {
        $this->assignedCustomerIdList[] = $assignedCustomerId;
        return $this;
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
