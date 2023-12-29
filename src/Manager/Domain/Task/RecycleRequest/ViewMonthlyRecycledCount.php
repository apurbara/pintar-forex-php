<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyRecycledCount implements ManagerTask
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->recycleRequestRepository->monthlyRecycledCount($manager->getId(), $payload->listSchema);
        $payload->setResult($result);
    }
}
