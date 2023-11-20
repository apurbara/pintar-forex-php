<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;

class RejectRecycleRequest implements ManagerTask
{
    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
    {
    }

    /**
     * 
     * @param Manager $manager
     * @param string $payload recycleRequestId
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $recycleRequest = $this->recycleRequestRepository->ofId($payload);
        $recycleRequest->assertManageableByManager($manager);
        
        $recycleRequest->reject();
    }
}
