<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\BySales\SalesActivitySchedule\SubmitScheduleTask;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\src\Sales\Domain\Task\BySales\SalesTaskTestBase;

class SubmitScheduleTaskTest extends SalesTaskTestBase
{

    protected $task, $schedulerService;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityScheduleDependency();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareSalesActivityDependency();
        
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);

        $this->task = new SubmitScheduleTask($this->salesActivityScheduleRepository,
                $this->customerAssignmentRepository, $this->salesActivityRepository, $this->schedulerService);

        //
        $timeIntervalData = new HourlyTimeIntervalData('next week');
        $this->payload = (new SalesActivityScheduleData($timeIntervalData))
                ->setCustomerAssignmentId($this->customerAssignmentId)
                ->setSalesActivityId($this->salesActivityId);
    }
    
    //
    protected function execute()
    {
        $this->salesActivityScheduleRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->salesActivityScheduleId);
        
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addScheduledCreatedInCustomerAssignmentToRepository()
    {
        $this->customerAssignment->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $this->payload)
                ->willReturn($this->salesActivitySchedule);
        $this->salesActivityScheduleRepository->expects($this->once())
                ->method('add')
                ->with($this->salesActivitySchedule);
        $this->execute();
    }
    public function test_execute_assertCustomerAssignmentBelongsToSales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->salesActivityScheduleId, $this->payload->id);
    }
    public function test_execute_registerAllUpcomingScheduleToScheduler()
    {
        $this->sales->expects($this->once())
                ->method('registerAllUpcomingScheduleToScheduler')
                ->with($this->schedulerService);
        $this->execute();
    }
    public function test_execute_attemptToRelocateConflictedInitialScheduleIfDurationNotEnough()
    {
        $this->customerAssignment->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $this->payload)
                ->willReturn($this->salesActivitySchedule);
        $this->salesActivitySchedule->expects($this->once())
                ->method('relocateConflictedInitialScheduleIfDurationNotEnough')
                ->with($this->schedulerService);
        $this->execute();
    }
}
