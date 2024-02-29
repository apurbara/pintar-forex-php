<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use Sales\Domain\Model\AreaStructure\Area\CustomerData;

readonly class UpdateCustomerPayload extends AbstractEntityMutationPayload
{

    public CustomerData $customerData;

    public function setCustomerData(CustomerData $customerData)
    {
        $this->customerData = $customerData;
        return $this;
    }
}
