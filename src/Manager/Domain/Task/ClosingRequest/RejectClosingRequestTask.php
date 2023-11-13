<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;

class RejectClosingRequestTask implements ManagerTask
{
    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
    {
    }

    /**
     * 
     * @param Manager $manager
     * @param string $payload closingRequestId
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $closingRequest = $this->closingRequestRepository->ofId($payload);
        $closingRequest->assertManageableByManager($manager);
        
        $closingRequest->reject();
    }
}
