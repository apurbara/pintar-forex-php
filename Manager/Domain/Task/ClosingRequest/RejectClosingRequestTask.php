<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequestData;
use Manager\Domain\Task\ManagerTask;

class RejectClosingRequestTask implements ManagerTask
{

    public function __construct(protected ClosingRequestRepository $repository)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param ClosingRequestData $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $closingRequest = $this->repository->ofId($payload->id);
        $closingRequest->assertBelongsToManager($manager);
        
        $closingRequest->reject($payload);
    }
}
