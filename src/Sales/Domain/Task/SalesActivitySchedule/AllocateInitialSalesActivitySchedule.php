<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;
use Sales\Domain\Task\SalesTask;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

class AllocateInitialSalesActivitySchedule implements SalesTask
{

    protected SalesActivitySchedulerService $schedulerService;

    public function __construct(
            protected SalesActivityScheduleRepository $scheduleRepository,
            protected AssignedCustomerRepository $assignedCustomerRepository,
            protected SalesActivityRepository $salesActivityRepository
    )
    {
        $this->schedulerService = new SalesActivitySchedulerService();
    }

    /**
     * 
     * @param Sales $sales
     * @param string $payload assignedCustomerId
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $sales->registerAllUpcomingScheduleToScheduler($this->schedulerService);

        $assignedCustomer = $this->assignedCustomerRepository->ofId($payload);
        $salesActivity = $this->salesActivityRepository->anInitialSalesActivity();
        if ($salesActivity) {
            $nextAvailableScheduleStartTime = $this->schedulerService
                    ->nextAvailableStartTimeForInitialSalesActivity($salesActivity);

            $hourlyTimeIntervalData = new HourlyTimeIntervalData($nextAvailableScheduleStartTime->format('Y-m-d H:i:s'));
            $scheduledSalesActivityData = (new Sales\AssignedCustomer\SalesActivityScheduleData($hourlyTimeIntervalData))
                    ->setId($this->scheduleRepository->nextIdentity());

            $scheduledSalesActivity = $assignedCustomer->submitSalesActivitySchedule($salesActivity,
                    $scheduledSalesActivityData);
            $this->scheduleRepository->add($scheduledSalesActivity);
        }
    }
}
