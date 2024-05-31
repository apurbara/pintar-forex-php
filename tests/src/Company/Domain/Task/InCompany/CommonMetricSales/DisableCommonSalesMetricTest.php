<?php

namespace Company\Domain\Task\InCompany\CommonSalesMetric;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class DisableCommonSalesMetricTest extends TaskInCompanyTestBase
{

    protected $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCommonSalesMetricDependency();
        //
        $this->task = new DisableCommonSalesMetric($this->commonSalesMetricRepository);
    }

    //
    protected function execute()
    {
        $this->task->executeInCompany($this->commonSalesMetricId);
    }

    public function test_execute_updateCommonMetricSales()
    {
        $this->commonSalesMetric->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
