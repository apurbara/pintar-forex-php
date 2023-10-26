<?php

namespace Sales\Domain\Task\ScheduledSalesActivity;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ScheduledSalesActivityData;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class SubmitScheduleTaskTest extends SalesTaskTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareScheduledSalesActivityDependency();
        $this->prepareAssignedCustomerDependency();
        $this->prepareSalesActivityDependency();

        $this->task = new SubmitScheduleTask($this->scheduledSalesActivityRepository,
                $this->assignedCustomerRepository, $this->salesActivityRepository);

        //
        $timeIntervalData = new HourlyTimeIntervalData('next week');
        $this->payload = (new ScheduledSalesActivityData($timeIntervalData))
                ->setAssignedCustomerId($this->assignedCustomerId)
                ->setSalesActivityId($this->salesActivityId);
    }
    
    //
    protected function execute()
    {
        $this->scheduledSalesActivityRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->scheduledSalesActivityId);
        
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addScheduledCreatedInAssignedCustomerToRepository()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('submitSalesActivitySchedule')
                ->with($this->salesActivity, $this->payload)
                ->willReturn($this->scheduledSalesActivity);
        $this->scheduledSalesActivityRepository->expects($this->once())
                ->method('add')
                ->with($this->scheduledSalesActivity);
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
        $this->assertSame($this->scheduledSalesActivityId, $this->payload->id);
    }
}
