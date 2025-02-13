<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Tests\Sales\Domain\Task\SalesTaskTestBase;

class SubmitNonScheduledSalesActivityReportTest extends SalesTaskTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityReportDependency();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareSalesActivityScheduleDependency();
        $this->prepareSalesActivityDependency();
        //
        $this->task = new SubmitNonScheduledSalesActivityReport(
                $this->salesActivityReportRepository, $this->customerAssignmentRepository,
                $this->salesActivityScheduleRepository, $this->salesActivityRepository);
        $this->payload = (new SubmitNonScheduledSalesActivityReportPayload('content'))
                ->setCustomerAssignmentId($this->customerAssignmentId)
                ->setSalesActivityId($this->salesActivityId);
    }

    //
    protected function execute()
    {
        $this->salesActivityReportRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->salesActivityReportId);
        $this->salesActivityScheduleRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->salesActivityScheduleId);
        $this->task->executeBySales($this->sales, $this->payload);
    }

    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->salesActivityReportId, $this->payload->id);
    }
    public function test_execute_setScheduleId()
    {
        $this->execute();
        $this->assertSame($this->salesActivityScheduleId, $this->payload->salesActivityScheduleId);
    }
    public function test_execute_addReportToRepository()
    {
        $this->salesActivityReportRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_assertCustomerAssignmentBelongsBySales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales');
        $this->execute();
    }
    public function test_execute_noSalesActivityId_useInitialSalesActivity()
    {
        $this->payload = (new SubmitNonScheduledSalesActivityReportPayload('content'))
                ->setCustomerAssignmentId($this->customerAssignmentId);
        
        $this->salesActivityRepository->expects($this->once())
                ->method('anInitialSalesActivity')
                ->willReturn($this->salesActivity);
        $this->execute();
    }
}
