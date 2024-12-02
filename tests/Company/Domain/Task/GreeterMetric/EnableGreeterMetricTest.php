<?php

namespace Company\Domain\Task\GreeterMetric;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class EnableGreeterMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareGreeterMetricDependency();
        //
        $this->task = new EnableGreeterMetric($this->greeterMetricRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->greeterMetricId);
    }
    public function test_execute_enableGreeterMetric()
    {
        $this->greeterMetric->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
