<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class SubmitScheduleTaskTest extends SalesTaskTestBase
{

    protected $task, $schedulerService;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityScheduleDependency();
        $this->prepareAssignedCustomerDependency();
        $this->prepareSalesActivityDependency();
        
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);

        $this->task = new SubmitScheduleTask($this->salesActivityScheduleRepository,
                $this->assignedCustomerRepository, $this->salesActivityRepository, $this->schedulerService);

        //
        $timeIntervalData = new HourlyTimeIntervalData('next week');
        $this->payload = (new SalesActivityScheduleData($timeIntervalData))
                ->setAssignedCustomerId($this->assignedCustomerId)
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
    public function test_execute_addScheduledCreatedInAssignedCustomerToRepository()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $this->payload)
                ->willReturn($this->salesActivitySchedule);
        $this->salesActivityScheduleRepository->expects($this->once())
                ->method('add')
                ->with($this->salesActivitySchedule);
        $this->execute();
    }
    public function test_execute_assertAssignedCustomerBelongsToSales()
    {
        $this->assignedCustomer->expects($this->once())
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
        $this->assignedCustomer->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $this->payload)
                ->willReturn($this->salesActivitySchedule);
        $this->salesActivitySchedule->expects($this->once())
                ->method('relocateConflictedInitialScheduleIfDurationNotEnough')
                ->with($this->schedulerService);
        $this->execute();
    }
}
