<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use Sales\Domain\Task\SalesTask;

class SubmitScheduleTask implements SalesTask
{

    public function __construct(
            protected SalesActivityScheduleRepository $scheduledSalesActivityRepository,
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected SalesActivityRepository $salesActivityRepository,
            protected SalesActivitySchedulerService $schedulerService
    )
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
        $sales->registerAllUpcomingScheduleToScheduler($this->schedulerService);

        $payload->setId($this->scheduledSalesActivityRepository->nextIdentity());

        $salesActivity = $this->salesActivityRepository->ofId($payload->salesActivityId);
        $customerAssignment = $this->customerAssignmentRepository->ofId($payload->customerAssignmentId);
        $customerAssignment->assertBelongsToSales($sales);

        $scheduledSalesActivity = $customerAssignment->submitSalesActivitySchedule($salesActivity, $payload);
        $scheduledSalesActivity->relocateConflictedInitialScheduleIfDurationNotEnough($this->schedulerService);

        $this->scheduledSalesActivityRepository->add($scheduledSalesActivity);
    }
}
