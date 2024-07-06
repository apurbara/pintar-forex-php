<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use DateTimeImmutable;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class AllocateInitialSalesActivityScheduleTest extends SalesTaskTestBase
{
    protected $task, $schedulerService;
    //
    protected $availableInitialScheduleStartTime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityScheduleDependency();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareSalesActivityDependency();
        
        $this->task = new TestableAlocateInitialSalesActivitySchedule($this->salesActivityScheduleRepository, $this->customerAssignmentRepository, $this->salesActivityRepository);
        
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
        
        $this->task->executeBySales($this->sales, $this->customerAssignmentId);
    }
    public function test_execute_addInitialSchedulerFromCustomerAssignmentToRepository()
    {
        $this->salesActivity->expects($this->once())
                ->method('findAvailableTimeSlotForInitialActivity')
                ->with($this->schedulerService)
                ->willReturn($this->availableInitialScheduleStartTime);
        
        $hourlyTimeIntervalData = new HourlyTimeIntervalData($this->availableInitialScheduleStartTime->format('Y-m-d H:i:s'));
        $data = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId($this->salesActivityScheduleId);
        $this->customerAssignment->expects($this->once())
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
