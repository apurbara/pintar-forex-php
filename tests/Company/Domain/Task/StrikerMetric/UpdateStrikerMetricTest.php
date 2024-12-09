<?php

namespace Company\Domain\Task\StrikerMetric;

use Company\Domain\Model\StrikerMetricData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UpdateStrikerMetricTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareStrikerMetricDependency();
        //
        $this->task = new UpdateStrikerMetric($this->strikerMetricRepository);
        $this->payload = (new StrikerMetricData())
                ->setId($this->strikerMetricId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateStrikerMetric()
    {
        $this->strikerMetric->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
