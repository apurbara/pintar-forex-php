<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class AllocateInitialSalesActivityScheduleForMultipleAssignmentTest extends SalesTaskTestBase
{
    protected MockObject $customerAssignmentRepository;
    protected MockObject $customerAssignmentOne, $customerAssignmentTwo;
    protected string $customerAssignmentOneId = 'customerAssignmentOneId', $customerAssignmentTwoId = 'customerAssignmentTwoId';
    protected $task, $schedulerService;
    //
    protected $payload;
    protected $availableInitialScheduleStartTime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityScheduleDependency();
        $this->prepareSalesActivityDependency();
        
        $this->customerAssignmentRepository = $this->buildMockOfInterface(CustomerAssignmentRepository::class);
        $this->customerAssignmentOne = $this->buildMockOfClass(CustomerAssignment::class);
        $this->customerAssignmentTwo = $this->buildMockOfClass(CustomerAssignment::class);
        
        $this->customerAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->willReturnCallback(fn($customerId) => match ($customerId){
                    $this->customerAssignmentOneId => $this->customerAssignmentOne,
                    $this->customerAssignmentTwoId => $this->customerAssignmentTwo,
                    default => null,
                });
        
        $this->task = new TestableAllocateInitialSalesActivityScheduleForMultipleAssignment($this->salesActivityScheduleRepository, $this->customerAssignmentRepository, $this->salesActivityRepository);
        
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
        $this->task->schedulerService = $this->schedulerService;
        //
        $this->payload = [$this->customerAssignmentOneId, $this->customerAssignmentTwoId];
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
    public function test_execute_addInitialSchedulerFromCustomerAssignmentToRepository()
    {
        $this->salesActivity->expects($this->exactly(2))
                ->method('findAvailableTimeSlotForInitialActivity')
                ->with($this->schedulerService)
                ->willReturn($this->availableInitialScheduleStartTime);
        
        $hourlyTimeIntervalData = new HourlyTimeIntervalData($this->availableInitialScheduleStartTime->format('Y-m-d H:i:s'));
        $data = (new SalesActivityScheduleData($hourlyTimeIntervalData))->setId($this->salesActivityScheduleId);
        $this->customerAssignmentOne->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $data);
        $this->customerAssignmentTwo->expects($this->once())
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
        $this->customerAssignmentOne->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->customerAssignmentTwo->expects($this->once())
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
