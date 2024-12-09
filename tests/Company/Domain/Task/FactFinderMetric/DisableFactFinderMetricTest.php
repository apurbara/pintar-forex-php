<?php

namespace Company\Domain\Task\FactFinderMetric;

use Company\Domain\Model\FactFinderMetricData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class DisableFactFinderMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFinderMetricDependency();
        //
        $this->task = new DisableFactFinderMetric($this->factFinderMetricRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->factFinderMetricId);
    }
    public function test_execute_disableFactFinderMetric()
    {
        $this->factFinderMetric->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
