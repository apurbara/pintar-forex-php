<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewSummaryPayload;

class ViewClosingRequestCount implements ManagerTask
{
    public function __construct(protected ClosingRequestRepository $repository)
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
        $result = $this->repository->closingRequestCountBelongsToManager($manager->getId(), $payload->searchSchema);
        $payload->setResult($result);
    }
}
