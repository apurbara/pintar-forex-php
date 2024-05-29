<?php

namespace Sales\Domain\Task\BySales\CustomerAssignment;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use Sales\Domain\DependencyModel\AreaStructure\Area\CustomerData;

readonly class RegisterNewCustomerPayload extends AbstractEntityMutationPayload
{

    public function __construct(public string $areaId, public CustomerData $customerData)
    {
        
    }
}
