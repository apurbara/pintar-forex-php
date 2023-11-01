<?php

namespace Sales\Domain\Task\RecycleRequest;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequestData;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesTask;

class SubmitRecycleRequestTask implements SalesTask
{

    public function __construct(
            protected RecycleRequestRepository $closingRequestRepository,
            protected AssignedCustomerRepository $assignedCustomerRepository)
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
        
        $assignedCustomer = $this->assignedCustomerRepository->ofId($payload->assignedCustomerId);
        $assignedCustomer->assertBelongsToSales($sales);
        
        $closingRequest = $assignedCustomer->submitRecycleRequest($payload);
        $this->closingRequestRepository->add($closingRequest);
    }
}
