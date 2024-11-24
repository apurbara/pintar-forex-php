<?php

namespace Sales\Domain\Task\StrikingAssignment;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\CustomerJourneyRepository;
use Sales\Domain\Task\SalesTask;

class UpdateJourney implements SalesTask
{

    public function __construct(
            protected StrikingAssignmentRepository $repository,
            protected CustomerJourneyRepository $customerJourneyRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param UpdateJourneyPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $strikingAssignment = $this->repository->ofId($payload->id);
        $customerJourney = $this->customerJourneyRepository->ofId($payload->customerJourneyId);
        
        $strikingAssignment->assertBelongsToSales($sales);
        $strikingAssignment->updateJourney($customerJourney);
    }
}
