<?php

namespace SharedContext\Domain\Event;

use Resources\Event\EventInterface;

class MultipleCustomerAssignmentReceivedBySales implements EventInterface
{

    const NAME = "MultipleCustomerAssignmentReceivedBySales";
    protected array $customerAssignmentIdList = [];
    
    public function __construct(public readonly string $salesId)
    {
    }

    public function getCustomerAssignmentIdList(): array
    {
        return $this->customerAssignmentIdList;
    }

    //
    public function addCustomerAssignmentId(string $customerAssignmentId)
    {
        $this->customerAssignmentIdList[] = $customerAssignmentId;
        return $this;
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
