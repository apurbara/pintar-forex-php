<?php

namespace Company\Domain\Task\StrikerMetric;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class EnableStrikerMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareStrikerMetricDependency();
        //
        $this->task = new EnableStrikerMetric($this->strikerMetricRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->strikerMetricId);
    }
    public function test_execute_enableStrikerMetric()
    {
        $this->strikerMetric->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
