<?php

namespace Company\Domain\Task\InCompany\SalesPerformanceMetric;

use Company\Domain\Model\SalesPerformanceMetricData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class UpdateSalesPerformanceMetricTest extends TaskInCompanyTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        $this->prepareSalesPerformanceMetricDependency();
        //
        $this->task = new UpdateSalesPerformanceMetric($this->salesPerformanceMetricRepository);
        $this->payload = (new SalesPerformanceMetricData())
                ->setId($this->salesPerformanceMetricId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_addSalesPerformanceMetricToRepository()
    {
        $this->salesPerformanceMetric->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
