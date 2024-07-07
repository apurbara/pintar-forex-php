<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\AdminTaskInCompany;

class CancelCustomerAssignment implements AdminTaskInCompany
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
