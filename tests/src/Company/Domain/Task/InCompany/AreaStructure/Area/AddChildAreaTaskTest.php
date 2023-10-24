<?php

namespace Company\Domain\Task\InCompany\AreaStructure\Area;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\AreaStructure\AreaData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddChildAreaTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    protected $childArea, $childAreaId = 'childAreaId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAreaDependency();
        $this->prepareAreaStructureDependency();
        $this->task = new AddChildAreaTask($this->areaRepository, $this->areaStructureRepository);
        //
        $this->payload = (new AreaData($this->createLabelData()))
                ->setParentAreaId($this->areaId)
                ->setAreaStructureId($this->areaStructureId);
        
        $this->childArea = $this->buildMockOfClass(Area::class);
    }
    
    //
    protected function executeInCompany()
    {
        $this->areaRepository->expects($this->any())
                ->method('isChildAreaNameAvailable')
                ->willReturn(true);
        $this->areaRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->childAreaId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_executeInCompany_addChildCreatedInParentToRepository()
    {
        $this->area->expects($this->once())
                ->method('createChild')
                ->with($this->areaStructure, $this->payload)
                ->willReturn($this->childArea);
        $this->areaRepository->expects($this->once())
                ->method('add')
                ->with($this->childArea);
        $this->executeInCompany();
    }
    public function test_executeInCompany_setPayloadId()
    {
        $this->executeInCompany();
        $this->assertSame($this->childAreaId, $this->payload->id);
    }
    public function test_executeInCompany_nameUnavailableInParentArea()
    {
        $this->areaRepository->expects($this->once())
                ->method('isChildAreaNameAvailable')
                ->with($this->areaId, $this->payload->labelData->name)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->executeInCompany(), 'Conflict', 'area name is unavailable');
    }
    public function test_execute_assertParentActive()
    {
        $this->area->expects($this->once())
                ->method('assertActive');
        $this->executeInCompany();
    }
    public function test_execute_assertAreaStructureActive()
    {
        $this->areaStructure->expects($this->once())
                ->method('assertActive');
        $this->executeInCompany();
    }
}
