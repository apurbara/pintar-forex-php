<?php

namespace Company\Domain\Task\CompanyMetric;

use Company\Domain\Model\CompanyMetricData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UpdateCompanyMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCompanyMetricDependency();
        $this->task = new UpdateCompanyMetric($this->companyMetricRepository);
        $this->payload = (new CompanyMetricData())
                ->setId($this->companyMetricId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateCompanyMetric()
    {
        $this->companyMetric->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
