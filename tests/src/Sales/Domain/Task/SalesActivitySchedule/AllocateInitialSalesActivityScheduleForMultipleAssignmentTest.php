<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class AllocateInitialSalesActivityScheduleForMultipleAssignmentTest extends SalesTaskTestBase
{
    protected MockObject $assignedCustomerRepository;
    protected MockObject $assignedCustomerOne, $assignedCustomerTwo;
    protected string $assignedCustomerOneId = 'assignedCustomerOneId', $assignedCustomerTwoId = 'assignedCustomerTwoId';
    protected $task, $schedulerService;
    //
    protected $payload;
    protected $availableInitialScheduleStartTime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityScheduleDependency();
        $this->prepareSalesActivityDependency();
        
        $this->assignedCustomerRepository = $this->buildMockOfInterface(AssignedCustomerRepository::class);
        $this->assignedCustomerOne = $this->buildMockOfClass(AssignedCustomer::class);
        $this->assignedCustomerTwo = $this->buildMockOfClass(AssignedCustomer::class);
        
        $this->assignedCustomerRepository->expects($this->any())
                ->method('ofId')
                ->willReturnCallback(fn($customerId) => match ($customerId){
                    $this->assignedCustomerOneId => $this->assignedCustomerOne,
                    $this->assignedCustomerTwoId => $this->assignedCustomerTwo,
                    default => null,
                });
        
        $this->task = new TestableAllocateInitialSalesActivityScheduleForMultipleAssignment($this->salesActivityScheduleRepository, $this->assignedCustomerRepository, $this->salesActivityRepository);
        
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
        $this->task->schedulerService = $this->schedulerService;
        //
        $this->payload = [$this->assignedCustomerOneId, $this->assignedCustomerTwoId];
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
        
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addInitialSchedulerFromAssignedCustomerToRepository()
    {
        $this->salesActivity->expects($this->exactly(2))
                ->method('findAvailableTimeSlotForInitialActivity')
                ->with($this->schedulerService)
                ->willReturn($this->availableInitialScheduleStartTime);
        
        $hourlyTimeIntervalData = new HourlyTimeIntervalData($this->availableInitialScheduleStartTime->format('Y-m-d H:i:s'));
        $data = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId($this->salesActivityScheduleId);
        $this->assignedCustomerOne->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $data);
        $this->assignedCustomerTwo->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $data);
        
        $this->salesActivityScheduleRepository->expects($this->exactly(2))
                ->method('add');
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
    public function test_execute_assertCustomerBelongsToSales()
    {
        $this->assignedCustomerOne->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->assignedCustomerTwo->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    public function test_execute_addNewActivityScheduleToSchedulerService()
    {
        $this->schedulerService->expects($this->exactly(2))
                ->method('add');
        $this->execute();
    }
}

class TestableAllocateInitialSalesActivityScheduleForMultipleAssignment extends AllocateInitialSalesActivityScheduleForMultipleAssignment
{
    public SalesActivitySchedulerService $schedulerService;
}
