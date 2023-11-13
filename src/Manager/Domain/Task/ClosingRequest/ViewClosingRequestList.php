<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewClosingRequestList implements ManagerTask
{

    public function __construct(protected ClosingRequestRepository $closingRequestRepository)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->closingRequestRepository
                ->closingRequestListBelongToManager($manager->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
