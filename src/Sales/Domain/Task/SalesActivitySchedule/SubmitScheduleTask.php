<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;
use Sales\Domain\Task\SalesTask;

class SubmitScheduleTask implements SalesTask
{

    public function __construct(
            protected SalesActivityScheduleRepository $scheduledSalesActivityRepository,
            protected AssignedCustomerRepository $assignedCustomerRepository,
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
        $assignedCustomer = $this->assignedCustomerRepository->ofId($payload->assignedCustomerId);
        $assignedCustomer->assertBelongsToSales($sales);
        
        $scheduledSalesActivity = $assignedCustomer->submitSalesActivitySchedule($salesActivity, $payload);
        $scheduledSalesActivity->relocateConflictedInitialScheduleIfDurationNotEnough($this->schedulerService);
        
        $this->scheduledSalesActivityRepository->add($scheduledSalesActivity);
    }
}
