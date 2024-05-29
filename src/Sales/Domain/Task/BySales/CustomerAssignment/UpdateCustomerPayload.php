<?php

namespace Sales\Domain\Task\BySales\CustomerAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use Sales\Domain\DependencyModel\AreaStructure\Area\CustomerData;

readonly class UpdateCustomerPayload extends AbstractEntityMutationPayload
{

    public CustomerData $customerData;

    public function setCustomerData(CustomerData $customerData)
    {
        $this->customerData = $customerData;
        return $this;
    }
}
