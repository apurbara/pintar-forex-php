<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Company\Domain\Model\AreaStructureData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddRootAreaStructureTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $areaStructureData, $labelData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAreaStructureDependency();
        $this->task = new AddRootAreaStructureTask($this->areaStructureRepository);
        //
        
        $this->areaStructureData = new AreaStructureData($this->createLabelData());
    }
    
    //
    protected function executeInCompany()
    {
        $this->areaStructureRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->areaStructureId);
        $this->areaStructureRepository->expects($this->any())
                ->method('isNameAvailable')
                ->willReturn(true);
        $this->task->executeInCompany($this->areaStructureData);
    }
    public function test_executeInCompany_addAreaInnovationToRepository()
    {
        $this->areaStructureRepository->expects($this->once())
                ->method('add');
        $this->executeInCompany();
    }
    public function test_executeInCompany_setAreaStructureId()
    {
        $this->executeInCompany();
        $this->assertSame($this->areaStructureId, $this->areaStructureData->id);
    }
    public function test_executeInCompany_nameUnavailable_conflict()
    {
        $this->areaStructureRepository->expects($this->once())
                ->method('isNameAvailable')
                ->with($this->areaStructureData->labelData->name)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->executeInCompany(), 'Conflict', 'name is not available');
    }
}
