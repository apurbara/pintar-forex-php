<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Model\Sales\CustomerAssignment\RecycleRequestData;
use Resources\Event\Dispatcher;

class ApproveRecycleRequest implements ManagerTaskInCompany
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository,
            protected Dispatcher $dispatcher)
    {
        
    }

    /**
     * 
     * @param RecycleRequestData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $recycleRequest = $this->recycleRequestRepository->ofId($payload->id);
        $recycleRequest->approve($payload);
        
        $this->dispatcher->dispatchEventContainer($recycleRequest);
    }
}
