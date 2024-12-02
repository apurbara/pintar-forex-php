<?php

namespace Company\Domain\Task\GreeterMetric;

use Company\Domain\Model\GreeterMetricData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UpdateGreeterMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareGreeterMetricDependency();
        //
        $this->task = new UpdateGreeterMetric($this->greeterMetricRepository);
        $this->payload = (new GreeterMetricData())
                ->setId($this->greeterMetricId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateGreeterMetric()
    {
        $this->greeterMetric->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
