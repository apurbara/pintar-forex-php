<?php

namespace Sales\Domain\Task\ScheduledSalesActivity;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ScheduledSalesActivityData;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;
use Sales\Domain\Task\SalesTask;

class SubmitScheduleTask implements SalesTask
{

    public function __construct(
            protected ScheduledSalesActivityRepository $scheduledSalesActivityRepository,
            protected AssignedCustomerRepository $assignedCustomerRepository,
            protected SalesActivityRepository $salesActivityRepository
    )
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ScheduledSalesActivityData $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setId($this->scheduledSalesActivityRepository->nextIdentity());
        
        $salesActivity = $this->salesActivityRepository->ofId($payload->salesActivityId);
        $assignedCustomer = $this->assignedCustomerRepository->ofId($payload->assignedCustomerId);
        $assignedCustomer->assertBelongsToSales($sales);
        
        $scheduledSalesActivity = $assignedCustomer->submitSalesActivitySchedule($salesActivity, $payload);
        $this->scheduledSalesActivityRepository->add($scheduledSalesActivity);
    }
}
