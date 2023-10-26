<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Resources\Domain\TaskPayload\AbstractEntityMutationPayload;
use Sales\Domain\Model\AreaStructure\Area\CustomerData;

readonly class RegisterNewCustomerPayload extends AbstractEntityMutationPayload
{

    public function __construct(public string $areaId, public CustomerData $customerData)
    {
        
    }
}
