<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\AdminTaskInCompany;

class CancelFactFindingAssignment implements AdminTaskInCompany
{

    public function __construct(protected FactFindingAssignmentRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload assignmentId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->cancel();
    }
}
