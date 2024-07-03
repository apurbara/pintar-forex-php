<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewMonthlyRecycledCount implements ManagerTask
{

    public function __construct(protected RecycleRequestRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewSummaryPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->repository->monthlyRecycledCountBelongsToManager($manager->getId(), $payload->searchSchema);
        $payload->setResult($result);
    }
}
