<?php

namespace Manager\Domain\Task\ClosingRequestByFactFinder;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewClosingRequestByFactFinderList implements ManagerTask
{

    public function __construct(protected ClosingRequestByFactFinderRepository $repository)
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
        $result = $this->repository->closingRequestByFactFinderListBelongsToManager($manager->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
