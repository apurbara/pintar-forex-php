<?php

namespace Manager\Domain\Task\CustomerAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;

class CancelCustomerAssignment implements ManagerTask
{

    public function __construct(protected CustomerAssignmentRepository $repository)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param string $payload customerAssignmentId
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $customerAssignment = $this->repository->ofId($payload);
        $customerAssignment->assertBelongsToManager($manager);
        
        $customerAssignment->cancel();
    }
}
