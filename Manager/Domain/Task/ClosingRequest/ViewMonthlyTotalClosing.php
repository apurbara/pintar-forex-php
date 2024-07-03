<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyTotalClosing implements ManagerTask
{

    public function __construct(protected ClosingRequestRepository $repository)
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
        $result = $this->repository->monthlyTotalClosingBelongsToManager($manager->getId(), $payload->listSchema);
        $payload->setResult($result);
    }
}
