<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructureData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddChildAreaStructureTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $areaStructureData;
    protected $child, $childId = 'childId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAreaStructureDependency();
        $this->task = new AddChildAreaStructureTask($this->areaStructureRepository);
        //
        $this->areaStructureData = (new AreaStructureData($this->createLabelData()))
                ->setParentId($this->areaStructureId);
        //
        $this->child = $this->buildMockOfClass(AreaStructure::class);
    }
    
    //
    protected function executeInCompany()
    {
        $this->areaStructureRepository->expects($this->any())
                ->method('isNameAvailable')
                ->willReturn(true);
        $this->areaStructureRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->childId);
        $this->task->executeInCompany($this->areaStructureData);
    }
    public function test_executeInCompany_addChildAreaStructureToRepository()
    {
        $this->areaStructureRepository->expects($this->any())
                ->method('isNameAvailable')
                ->willReturn(true);
        $this->areaStructure->expects($this->once())
                ->method('createChild')
                ->with($this->areaStructureData)
                ->willReturn($this->child);
        $this->areaStructureRepository->expects($this->once())
                ->method('add')
                ->with($this->child);
        $this->executeInCompany();
    }
    public function test_executeInCompany_setAreaStructureDataId()
    {
        $this->executeInCompany();
        $this->assertSame($this->childId, $this->areaStructureData->id);
    }
    public function test_executeInCompany_nameUnavailable_forbidden()
    {
        $this->areaStructureRepository->expects($this->once())
                ->method('isNameAvailable')
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->executeInCompany(), 'Conflict', 'name is unavailable');
    }
}
