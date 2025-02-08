<?php

namespace Manager\Domain\Task\ClosingRequestByFactFinder;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewClosingRequestByFactFinderDetail implements ManagerTask
{

    public function __construct(protected ClosingRequestByFactFinderRepository $repository)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->repository->aClosingRequestByFactFinderBelongsToManager($manager->getId(), $payload->id);
        $payload->setResult($result);
    }
}
