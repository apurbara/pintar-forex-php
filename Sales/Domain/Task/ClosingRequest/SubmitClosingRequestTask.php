<?php

namespace Sales\Domain\Task\ClosingRequest;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequestData;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\SalesTask;

class SubmitClosingRequestTask implements SalesTask
{

    public function __construct(
            protected ClosingRequestRepository $closingRequestRepository,
            protected CustomerAssignmentRepository $customerAssignmentRepository)
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

        $customerAssignment = $this->customerAssignmentRepository->ofId($payload->customerAssignmentId);
        $customerAssignment->assertBelongsToSales($sales);

        $closingRequest = $customerAssignment->submitClosingRequest($payload);
        $this->closingRequestRepository->add($closingRequest);
    }
}
