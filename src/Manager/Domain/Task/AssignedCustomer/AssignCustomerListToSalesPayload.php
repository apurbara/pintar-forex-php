<?php

namespace Manager\Domain\Task\AssignedCustomer;

class AssignCustomerListToSalesPayload
{

    protected array $customerIdList;
    protected array $salesIdList;

    public function __construct()
    {
        
    }

    public function getCustomerIdList(): array
    {
        return $this->customerIdList;
    }

    public function getSalesIdList(): array
    {
        return $this->salesIdList;
    }

    public function addCustomer(string $customerId): self
    {
        $this->customerIdList[] = $customerId;
        return $this;
    }

    public function addSales(string $salesId): self
    {
        $this->salesIdList[] = $salesId;
        return $this;
    }
}
