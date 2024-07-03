<?php

namespace Company\Domain\Task\SalesPerformanceMetric;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class EnableSalesPerformanceMetricTest extends TaskInCompanyTestBase
{

    protected $task;

    protected function setUp(): void
    {
        $this->prepareSalesPerformanceMetricDependency();
        //
        $this->task = new EnableSalesPerformanceMetric($this->salesPerformanceMetricRepository);
    }

    //
    protected function execute()
    {
        $this->task->executeInCompany($this->salesPerformanceMetricId);
    }
    public function test_execute_addSalesPerformanceMetricToRepository()
    {
        $this->salesPerformanceMetric->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
