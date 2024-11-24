<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Sales\ContainCustomerAssignmentInterface;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;
use Sales\Domain\Task\SalesActivitySchedule\SubmitScheduleTask;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class SubmitScheduleTaskTest extends SalesTaskTestBase
{
    protected $containCustomerAssignmentRepository, $containCustomerAssignment, $customerAssignmentId = 'customerAssignmentId';
    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityScheduleDependency();
        $this->prepareSalesActivityDependency();
        $this->containCustomerAssignmentRepository = $this->buildMockOfInterface(ContainCustomerAssignmentRepository::class);
        $this->containCustomerAssignment = $this->buildMockOfInterface(ContainCustomerAssignmentInterface::class);
        $this->containCustomerAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerAssignmentId)
                ->willReturn($this->containCustomerAssignment);
        
        $this->task = new SubmitScheduleTask($this->salesActivityScheduleRepository,
                $this->containCustomerAssignmentRepository, $this->salesActivityRepository);

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
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->salesActivityScheduleId, $this->payload->id);
    }
    public function test_execute_addScheduledCreatedInCustomerAssignmentToRepository()
    {
        $this->containCustomerAssignment->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $this->salesActivityScheduleId, $this->payload)
                ->willReturn($this->salesActivitySchedule);
        $this->salesActivityScheduleRepository->expects($this->once())
                ->method('add')
                ->with($this->salesActivitySchedule);
        $this->execute();
    }
    public function test_execute_assertCustomerAssignmentBelongsToSales()
    {
        $this->containCustomerAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
}
