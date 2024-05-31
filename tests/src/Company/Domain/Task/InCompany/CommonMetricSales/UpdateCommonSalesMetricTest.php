<?php

namespace Company\Domain\Task\InCompany\CommonSalesMetric;

use Company\Domain\Model\CommonSalesMetricData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class UpdateCommonSalesMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCommonSalesMetricDependency();
        //
        $this->task = new UpdateCommonSalesMetric($this->commonSalesMetricRepository);
        $this->payload = (new CommonSalesMetricData())
                ->setId($this->commonSalesMetricId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateCommonMetricSales()
    {
        $this->commonSalesMetric->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
