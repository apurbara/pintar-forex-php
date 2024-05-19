<?php

namespace Company\Domain\Task\InCompany\Area;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class UpdateAreaTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAreaDependency();
        //
        $this->task = new UpdateArea($this->areaRepository);
        $this->payload = (new \Company\Domain\Model\AreaStructure\AreaData($this->createLabelData()))
                ->setId($this->areaId);
    }
    
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateArea()
    {
        $this->area->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
