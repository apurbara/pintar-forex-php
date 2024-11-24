<?php

namespace Manager\Domain\Task\CustomerAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCustomerAssignmentList implements ManagerTask
{

    public function __construct(protected CustomerAssignmentRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->repository->customerAssignmentListBelongsByManager($manager->getId(),
                $payload->paginationSchema);
        $payload->setResult($result);
    }
}
