<?php

namespace Company\Domain\Task\Customer;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerList implements ManagerTaskInCompany
{
    public function __construct(protected CustomerRepository $customerRepository)
    {
    }
    
    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->customerRepository->customerList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
