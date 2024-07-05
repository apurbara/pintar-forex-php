<?php

namespace Sales\Domain\Task\SalesActivityReport;

use DateTime;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class SubmitInitialSalesActivityReportTest extends SalesTaskTestBase
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
        $this->task = new SubmitInitialSalesActivityReport(
                $this->salesActivityReportRepository, $this->customerAssignmentRepository,
                $this->salesActivityScheduleRepository, $this->salesActivityRepository);
        $this->payload = (new SubmitInitialSalesActivityReportPayload('content'))
                ->setCustomerAssignmentId($this->customerAssignmentId);
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
        $this->salesActivityRepository->expects($this->any())
                ->method('anInitialSalesActivity')
                ->willReturn($this->salesActivity);
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
}
