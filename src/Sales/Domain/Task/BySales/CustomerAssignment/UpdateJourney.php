<?php

namespace Sales\Domain\Task\BySales\CustomerAssignment;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignmentData;
use Sales\Domain\Task\Dependency\CustomerJourneyRepository;
use Sales\Domain\Task\BySales\SalesTask;

class UpdateJourney implements SalesTask
{

    public function __construct(
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected CustomerJourneyRepository $customerJourneyRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param CustomerAssignmentData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $customerAssignment = $this->customerAssignmentRepository->ofId($payload->id);
        $customerAssignment->assertBelongsToSales($sales);

        $customerJourney = $this->customerJourneyRepository->ofId($payload->customerJourneyId);

        $customerAssignment->updateJourney($customerJourney);
    }
}
