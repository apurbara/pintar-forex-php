<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use Sales\Domain\Task\SalesTask;


class SubmitScheduleTask implements SalesTask
{

    public function __construct(
            protected SalesActivityScheduleRepository $repository,
            protected ContainCustomerAssignmentRepository $containCustomerAssignmentRepository,
            protected SalesActivityRepository $salesActivityRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param SalesActivityScheduleData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setId($this->repository->nextIdentity());
//
        $salesActivity = $this->salesActivityRepository->ofId($payload->salesActivityId);
        $customerAssignment = $this->containCustomerAssignmentRepository->ofId($payload->customerAssignmentId);
        $customerAssignment->assertBelongsToSales($sales);
//
        $scheduledSalesActivity = $customerAssignment->submitSalesActivitySchedule($salesActivity, $payload->id, $payload);
//
        $this->repository->add($scheduledSalesActivity);
    }
}
