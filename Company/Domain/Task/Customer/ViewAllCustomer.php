<?php

namespace Company\Domain\Task\Customer;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllCustomer implements AdminTaskInCompany, ManagerTaskInCompany
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
