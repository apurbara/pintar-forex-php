<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequestData;
use Manager\Domain\Task\ManagerTask;
use Resources\Event\Dispatcher;

class ApproveRecycleRequest implements ManagerTask
{

    public function __construct(protected RecycleRequestRepository $recycleRequestRepository,
            protected Dispatcher $dispatcher)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param RecycleRequestData $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $recycleRequest = $this->recycleRequestRepository->ofId($payload->id);
        $recycleRequest->assertManageableByManager($manager);

        $recycleRequest->approve($payload);
        
        $this->dispatcher->dispatchEventContainer($recycleRequest);
    }
}
