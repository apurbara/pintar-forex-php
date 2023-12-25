<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewMonthlyTotalClosing implements ManagerTask
{

    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
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
        $result = $this->closingRequestRepository->monthlyTotalClosing($manager->getId(), $payload->listSchema);
        $payload->setResult($result);
    }
}
