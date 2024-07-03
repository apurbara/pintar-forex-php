<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewRecycleRequestDetail implements ManagerTask
{

    public function __construct(protected RecycleRequestRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->repository->aRecycleRequestBelongsToManager($manager->getId(), $payload->id);
        $payload->setResult($result);
    }
}
