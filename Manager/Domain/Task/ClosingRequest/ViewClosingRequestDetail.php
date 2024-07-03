<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewClosingRequestDetail implements ManagerTask
{

    public function __construct(protected ClosingRequestRepository $repository)
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
        $result = $this->repository->aClosingRequestBelongsToManager($manager->getId(), $payload->id);
        $payload->setResult($result);
    }
}
