<?php

namespace Sales\Domain\Task\BySales\RecycleRequest;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequestData;
use Sales\Domain\Task\BySales\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\BySales\SalesTask;

class SubmitRecycleRequestTask implements SalesTask
{

    public function __construct(
            protected RecycleRequestRepository $closingRequestRepository,
            protected CustomerAssignmentRepository $customerAssignmentRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param RecycleRequestData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setId($this->closingRequestRepository->nextIdentity());

        $customerAssignment = $this->customerAssignmentRepository->ofId($payload->customerAssignmentId);
        $customerAssignment->assertBelongsToSales($sales);

        $closingRequest = $customerAssignment->submitRecycleRequest($payload);
        $this->closingRequestRepository->add($closingRequest);
    }
}
