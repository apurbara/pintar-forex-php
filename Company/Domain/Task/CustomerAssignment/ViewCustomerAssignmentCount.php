<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewCustomerAssignmentCount implements ManagerTaskInCompany
{
    public function __construct(protected CustomerAssignmentRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewSummaryPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->repository->assignmentCount($payload->searchSchema));
    }
}
