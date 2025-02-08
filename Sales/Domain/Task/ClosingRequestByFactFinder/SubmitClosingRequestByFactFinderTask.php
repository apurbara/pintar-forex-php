<?php

namespace Sales\Domain\Task\ClosingRequestByFactFinder;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinderData;
use Sales\Domain\Task\SalesTask;
use Sales\Domain\Task\FactFindingAssignment\FactFindingAssignmentRepository;

class SubmitClosingRequestByFactFinderTask implements SalesTask
{

    public function __construct(
            protected ClosingRequestByFactFinderRepository $closingRequestByFactFinderRepository,
            protected FactFindingAssignmentRepository $factFindingAssignmentRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ClosingRequestByFactFinderData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setId($this->closingRequestByFactFinderRepository->nextIdentity());

        $factFindingAssignment = $this->factFindingAssignmentRepository->ofId($payload->factFindingAssignmentId);
        $factFindingAssignment->assertBelongsToSales($sales);

        $closingRequestByFactFinder = new Sales\FactFindingAssignment\ClosingRequestByFactFinder($factFindingAssignment, $payload->id, $payload);
        $this->closingRequestByFactFinderRepository->add($closingRequestByFactFinder);
    }
}
