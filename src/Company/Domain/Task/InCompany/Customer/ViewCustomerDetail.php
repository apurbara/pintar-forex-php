<?php

namespace Company\Domain\Task\InCompany\Customer;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCustomerDetail implements ManagerTaskInCompany
{
    public function __construct(protected CustomerRepository $customerRepository)
    {
    }
    
    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->customerRepository->aCustomer($payload->id);
        $payload->setResult($result);
    }
}
