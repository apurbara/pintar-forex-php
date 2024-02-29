<?php

namespace Company\Domain\Task\InCompany\Customer;

use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllCustomer implements PersonnelHavingManagerAssignmentTaskInCompany
{

    public function __construct(protected CustomerRepository $customerRepository)
    {
        
    }

    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->customerRepository->allCustomer($payload->listSchema);
        $payload->setResult($result);
    }
}
