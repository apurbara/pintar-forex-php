<?php

namespace Company\Domain\Task\CommonSalesMetric;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class EnableCommonSalesMetricTest extends TaskInCompanyTestBase
{

    protected $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCommonSalesMetricDependency();
        //
        $this->task = new EnableCommonSalesMetric($this->commonSalesMetricRepository);
    }

    //
    protected function execute()
    {
        $this->task->executeInCompany($this->commonSalesMetricId);
    }

    public function test_execute_updateCommonMetricSales()
    {
        $this->commonSalesMetric->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
