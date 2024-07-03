<?php

namespace Company\Domain\Task\CompanyMetric;

use Company\Domain\Model\CompanyMetricData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class DisableCompanyMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCompanyMetricDependency();
        $this->task = new DisableCompanyMetric($this->companyMetricRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->companyMetricId);
    }
    public function test_execute_updateCompanyMetric()
    {
        $this->companyMetric->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
