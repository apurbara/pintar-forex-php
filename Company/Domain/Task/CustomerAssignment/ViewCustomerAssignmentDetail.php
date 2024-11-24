<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCustomerAssignmentDetail implements AdminTaskInCompany
{

    public function __construct(protected CustomerAssignmentRepository $customerAssignmentRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->customerAssignmentRepository->aCustomerAssignment($payload->id);
        $payload->setResult($result);
    }
}
