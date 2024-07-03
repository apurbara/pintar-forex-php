<?php

namespace Manager\Domain\Task\CustomerAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewCustomerAssignmentCount implements ManagerTask
{
    public function __construct(protected CustomerAssignmentRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewSummaryPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->repository->assignmentCountBelongsToManager($manager->getId(), $payload->searchSchema);
        $payload->setResult($result);
    }

}
