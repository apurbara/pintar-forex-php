<?php

namespace Company\Domain\Task\InCompany\CustomerAssignment;

use Company\Domain\Model\ManagerTaskInCompany;

class CancelCustomerAssignment implements ManagerTaskInCompany
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
