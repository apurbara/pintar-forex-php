<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use DateTimeImmutable;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class AllocateInitialSalesActivityScheduleTest extends SalesTaskTestBase
{
    protected $task, $schedulerService;
    //
    protected $availableInitialScheduleStartTime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityScheduleDependency();
        $this->prepareAssignedCustomerDependency();
        $this->prepareSalesActivityDependency();
        
        $this->task = new TestableAlocateInitialSalesActivitySchedule($this->salesActivityScheduleRepository, $this->assignedCustomerRepository, $this->salesActivityRepository);
        
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
        $this->task->schedulerService = $this->schedulerService;
        //
        $this->availableInitialScheduleStartTime = new DateTimeImmutable('+24 hours');
    }
    
    //
    protected function execute()
    {
        $this->salesActivityScheduleRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->salesActivityScheduleId);
        $this->salesActivityRepository->expects($this->any())
                ->method('anInitialSalesActivity')
                ->willReturn($this->salesActivity);
        
        $this->task->executeBySales($this->sales, $this->assignedCustomerId);
    }
    public function test_execute_addInitialSchedulerFromAssignedCustomerToRepository()
    {
        $this->schedulerService->expects($this->once())
                ->method('nextAvailableStartTimeForInitialSalesActivity')
                ->with($this->salesActivity)
                ->willReturn($this->availableInitialScheduleStartTime);
        
        $hourlyTimeIntervalData = new HourlyTimeIntervalData($this->availableInitialScheduleStartTime->format('Y-m-d H:i:s'));
        $data = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId($this->salesActivityScheduleId);
        $this->assignedCustomer->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $data);
        $this->execute();
    }
    public function test_execute_registerSalesUpcomingScheduleToScheduler()
    {
        $this->sales->expects($this->once())
                ->method('registerAllUpcomingScheduleToScheduler')
                ->with($this->schedulerService);
        $this->execute();
    }
    public function test_execute_noInitialSalesActivity_void()
    {
        $this->salesActivity = null;
        $this->execute();
        $this->markAsSuccess();
    }
}

class TestableAlocateInitialSalesActivitySchedule extends AllocateInitialSalesActivitySchedule
{
    public SalesActivitySchedulerService $schedulerService;
}
