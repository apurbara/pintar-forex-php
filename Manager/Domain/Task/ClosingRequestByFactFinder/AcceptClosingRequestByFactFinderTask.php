<?php

namespace Manager\Domain\Task\ClosingRequestByFactFinder;

use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinderData;
use Manager\Domain\Task\ManagerTask;


class AcceptClosingRequestByFactFinderTask implements ManagerTask
{
    public function __construct(protected ClosingRequestByFactFinderRepository $repository)
    {
    }

    /**
     * 
     * @param Manager $manager
     * @param ClosingRequestByFactFinderData $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $closingRequestByFactFinder = $this->repository->ofId($payload->id);
        $closingRequestByFactFinder->assertBelongsToManager($manager);
        
        $closingRequestByFactFinder->accept($payload);
    }
}
