<?php

namespace Sales\Domain\Task\ClosingRequest;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequestData;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesTask;

class SubmitClosingRequestTask implements SalesTask
{

    public function __construct(
            protected ClosingRequestRepository $closingRequestRepository,
            protected AssignedCustomerRepository $assignedCustomerRepository)
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
        
        $assignedCustomer = $this->assignedCustomerRepository->ofId($payload->assignedCustomerId);
        $assignedCustomer->assertBelongsToSales($sales);
        
        $closingRequest = $assignedCustomer->submitClosingRequest($payload);
        $this->closingRequestRepository->add($closingRequest);
    }
}
