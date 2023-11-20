<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewRecycleRequestDetail implements ManagerTask
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository)
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
        $result = $this->recycleRequestRepository->aRecycleRequestBelongToManager($manager->getId(), $payload->id);
        $payload->setResult($result);
    }
}
