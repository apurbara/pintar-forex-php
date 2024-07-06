<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use Sales\Domain\Task\SalesTask;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;

class AllocateInitialSalesActivitySchedule implements SalesTask
{

    protected SalesActivitySchedulerService $schedulerService;

    public function __construct(
            protected SalesActivityScheduleRepository $scheduleRepository,
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected SalesActivityRepository $salesActivityRepository
    )
    {
        $this->schedulerService = new SalesActivitySchedulerService();
    }

    /**
     * 
     * @param Sales $sales
     * @param string $payload customerAssignmentId
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $sales->registerAllUpcomingScheduleToScheduler($this->schedulerService);

        $customerAssignment = $this->customerAssignmentRepository->ofId($payload);
        $salesActivity = $this->salesActivityRepository->anInitialSalesActivity();

        $availableTimeSlot = $salesActivity?->findAvailableTimeSlotForInitialActivity($this->schedulerService);
        if ($availableTimeSlot) {
            $hourlyTimeIntervalData = new HourlyTimeIntervalData($availableTimeSlot->format('Y-m-d H:i:s'));
            $scheduledSalesActivityData = (new SalesActivityScheduleData($hourlyTimeIntervalData))
                    ->setId($this->scheduleRepository->nextIdentity());

            $scheduledSalesActivity = $customerAssignment->submitSalesActivitySchedule($salesActivity,
                    $scheduledSalesActivityData);
            $this->scheduleRepository->add($scheduledSalesActivity);
        }
    }
}
