<?php

namespace Sales\Domain\Task\Customer;

use Sales\Domain\DependencyModel\CustomerData;

readonly class UpdateCustomerPayload
{

    public ?string $customerAssignmentId;
    public CustomerData $customerData;

    public function setCustomerAssignmentId(?string $customerAssignmentId)
    {
        $this->customerAssignmentId = $customerAssignmentId;
        return $this;
    }

    public function setCustomerData(CustomerData $customerData)
    {
        $this->customerData = $customerData;
        return $this;
    }
}
