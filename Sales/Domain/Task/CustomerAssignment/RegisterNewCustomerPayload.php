<?php

namespace Sales\Domain\Task\CustomerAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use Sales\Domain\DependencyModel\CustomerData;

readonly class RegisterNewCustomerPayload extends AbstractEntityMutationPayload
{

    public ?CustomerData $customerData;

    public function setCustomerData(?CustomerData $customerData)
    {
        $this->customerData = $customerData;
        return $this;
    }
}
