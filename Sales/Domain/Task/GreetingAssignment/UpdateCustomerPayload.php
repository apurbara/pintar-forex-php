<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use Sales\Domain\DependencyModel\CustomerData;

readonly class UpdateCustomerPayload extends AbstractEntityMutationPayload
{

    public CustomerData $customerData;

    public function setCustomerData(CustomerData $customerData)
    {
        $this->customerData = $customerData;
        return $this;
    }
}
