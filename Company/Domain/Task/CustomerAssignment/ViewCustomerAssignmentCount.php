<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewCustomerAssignmentCount implements AdminTaskInCompany
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
