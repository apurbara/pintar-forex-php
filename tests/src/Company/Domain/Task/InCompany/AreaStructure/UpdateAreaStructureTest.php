<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class UpdateAreaStructureTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAreaStructureDependency();
        //
        $this->task = new UpdateAreaStructure($this->areaStructureRepository);
        $this->payload = (new \Company\Domain\Model\AreaStructureData($this->createLabelData()))
                ->setId($this->areaStructureId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateAreaStructure()
    {
        $this->areaStructure->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
