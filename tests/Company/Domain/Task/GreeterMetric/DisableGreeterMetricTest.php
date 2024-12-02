<?php

namespace Company\Domain\Task\GreeterMetric;

use Company\Domain\Model\GreeterMetricData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class DisableGreeterMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareGreeterMetricDependency();
        //
        $this->task = new DisableGreeterMetric($this->greeterMetricRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->greeterMetricId);
    }
    public function test_execute_disableGreeterMetric()
    {
        $this->greeterMetric->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
