<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequestData;
use Manager\Domain\Task\ManagerTask;

class RejectRecycleRequest implements ManagerTask
{

    public function __construct(protected RecycleRequestRepository $repository)
    {
        
    }

    /**
     * 
     * @param RecycleRequestData $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $recycleRequest = $this->repository->ofId($payload->id);
        $recycleRequest->assertBelongsToManager($manager);
        
        $recycleRequest->reject($payload);
    }
}
