<?php

namespace Company\Domain\Task\StrikerMetric;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class DisableStrikerMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareStrikerMetricDependency();
        //
        $this->task = new DisableStrikerMetric($this->strikerMetricRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->strikerMetricId);
    }
    public function test_execute_disableStrikerMetric()
    {
        $this->strikerMetric->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
