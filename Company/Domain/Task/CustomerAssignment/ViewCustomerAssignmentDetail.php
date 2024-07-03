<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCustomerAssignmentDetail implements ManagerTaskInCompany
{

    public function __construct(protected CustomerAssignmentRepository $assignedCustomerRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->assignedCustomerRepository->aCustomerAssignment($payload->id);
        $payload->setResult($result);
    }
}
