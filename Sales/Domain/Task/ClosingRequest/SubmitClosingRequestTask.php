<?php

namespace Sales\Domain\Task\ClosingRequest;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\StrikingAssignment\ClosingRequestData;
use Sales\Domain\Task\SalesTask;
use Sales\Domain\Task\StrikingAssignment\StrikingAssignmentRepository;

class SubmitClosingRequestTask implements SalesTask
{

    public function __construct(
            protected ClosingRequestRepository $closingRequestRepository,
            protected StrikingAssignmentRepository $strikingAssignmentRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ClosingRequestData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setId($this->closingRequestRepository->nextIdentity());

        $strikingAssignment = $this->strikingAssignmentRepository->ofId($payload->strikingAssignmentId);
        $strikingAssignment->assertBelongsToSales($sales);

        $closingRequest = new Sales\StrikingAssignment\ClosingRequest($strikingAssignment, $payload->id, $payload);
        $this->closingRequestRepository->add($closingRequest);
    }
}
