<?php

namespace Company\Domain\Task\InCompany\Area;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class DisableAreaTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAreaDependency();
        //
        $this->task = new DisableArea($this->areaRepository);
    }
    
    protected function execute()
    {
        $this->task->executeInCompany($this->areaId);
    }
    public function test_execute_disableArea()
    {
        $this->area->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
