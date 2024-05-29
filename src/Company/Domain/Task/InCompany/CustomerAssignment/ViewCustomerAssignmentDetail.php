<?php

namespace Company\Domain\Task\InCompany\CustomerAssignment;

use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCustomerAssignmentDetail implements PersonnelTaskInCompany
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
