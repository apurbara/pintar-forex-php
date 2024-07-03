<?php

namespace Company\Domain\Task\CompanyMetric;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class EnableCompanyMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCompanyMetricDependency();
        $this->task = new EnableCompanyMetric($this->companyMetricRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->companyMetricId);
    }
    public function test_execute_updateCompanyMetric()
    {
        $this->companyMetric->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
