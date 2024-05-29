<?php

namespace Company\Domain\Task\InCompany\CustomerAssignment;

use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;

class CancelCustomerAssignment implements PersonnelHavingManagerAssignmentTaskInCompany
{

    public function __construct(protected CustomerAssignmentRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload customerAssignmentId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->cancel();
    }
}
