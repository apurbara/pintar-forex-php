<?php

namespace Manager\Domain\Task\ClosingRequestByFactFinder;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewClosingRequestByFactFinderCount implements ManagerTask
{
    public function __construct(protected ClosingRequestByFactFinderRepository $repository)
    {
    }

    /**
     * 
     * @param Manager $manager
     * @param ViewSummaryPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->repository->closingRequestByFactFinderCountBelongsToManager($manager->getId(), $payload->searchSchema);
        $payload->setResult($result);
    }
}
