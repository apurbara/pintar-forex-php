<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class DisableAreaStructureTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAreaStructureDependency();
        //
        $this->task = new DisableAreaStructure($this->areaStructureRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->areaStructureId);
    }
    public function test_execute_updateAreaStructure()
    {
        $this->areaStructure->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
