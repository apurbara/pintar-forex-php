<?php

namespace Company\Domain\Task\FactFinderMetric;

use Company\Domain\Model\FactFinderMetricData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UpdateFactFinderMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFinderMetricDependency();
        //
        $this->task = new UpdateFactFinderMetric($this->factFinderMetricRepository);
        $this->payload = (new FactFinderMetricData())
                ->setId($this->factFinderMetricId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateFactFinderMetric()
    {
        $this->factFinderMetric->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
