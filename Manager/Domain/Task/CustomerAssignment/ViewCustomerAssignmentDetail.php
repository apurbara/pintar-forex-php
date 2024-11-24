<?php

namespace Manager\Domain\Task\CustomerAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCustomerAssignmentDetail implements ManagerTask
{

    public function __construct(protected CustomerAssignmentRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->repository->aCustomerAssignmentBelongsByManager($manager->getId(), $payload->id);

        $payload->setResult($result);
    }
}
