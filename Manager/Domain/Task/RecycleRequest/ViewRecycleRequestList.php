<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewRecycleRequestList implements ManagerTask
{

    public function __construct(protected RecycleRequestRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->repository->recycleRequestListBelongsToManager($manager->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
