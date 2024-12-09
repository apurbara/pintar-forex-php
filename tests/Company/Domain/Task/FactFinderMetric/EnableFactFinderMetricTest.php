<?php

namespace Company\Domain\Task\FactFinderMetric;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class EnableFactFinderMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFinderMetricDependency();
        //
        $this->task = new EnableFactFinderMetric($this->factFinderMetricRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->factFinderMetricId);
    }
    public function test_execute_enableFactFinderMetric()
    {
        $this->factFinderMetric->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
