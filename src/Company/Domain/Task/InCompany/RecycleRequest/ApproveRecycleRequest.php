<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\Personnel\Sales\CustomerAssignment\RecycleRequestData;
use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;
use Resources\Event\Dispatcher;

class ApproveRecycleRequest implements PersonnelHavingManagerAssignmentTaskInCompany
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
