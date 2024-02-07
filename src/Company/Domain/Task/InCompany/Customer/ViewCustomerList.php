<?php

namespace Company\Domain\Task\InCompany\Customer;

use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerList implements PersonnelHavingManagerAssignmentTaskInCompany
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
