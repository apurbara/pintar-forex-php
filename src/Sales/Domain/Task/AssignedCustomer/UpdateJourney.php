<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomerData;
use Sales\Domain\Task\CustomerJourney\CustomerJourneyRepository;
use Sales\Domain\Task\SalesTask;

class UpdateJourney implements SalesTask
{

    public function __construct(
            protected AssignedCustomerRepository $assignedCustomerRepository,
            protected CustomerJourneyRepository $customerJourneyRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param AssignedCustomerData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $assignedCustomer = $this->assignedCustomerRepository->ofId($payload->id);
        $assignedCustomer->assertBelongsToSales($sales);
        
        $customerJourney = $this->customerJourneyRepository->ofId($payload->customerJourneyId);
        
        $assignedCustomer->updateJourney($customerJourney);
    }
}
