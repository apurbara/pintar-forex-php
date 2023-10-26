<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReportData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class SubmitSalesActivityReportTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityReportDependency();
        $this->prepareSalesActivityScheduleDependency();
        
        $this->task = new SubmitSalesActivityReportTask($this->salesActivityReportRepository, $this->salesActivityScheduleRepository);
        
        $this->payload = (new SalesActivityReportData('report content'))->setSalesActivityScheduleId($this->salesActivityScheduleId);
    }
    
    //
    protected function execute()
    {
        $this->salesActivityReportRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->salesActivityReportId);
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addReportCreatedInScheduleToRepository()
    {
        $this->salesActivitySchedule->expects($this->once())
                ->method('submitReport')
                ->with($this->payload)
                ->willReturn($this->salesActivityReport);
        $this->salesActivityReportRepository->expects($this->once())
                ->method('add')
                ->with($this->salesActivityReport);
        $this->execute();
    }
    public function test_execute_assertSalesActivityScheduleBelongsToSales()
    {
        $this->salesActivitySchedule->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->salesActivityReportId, $this->payload->id);
    }
}
