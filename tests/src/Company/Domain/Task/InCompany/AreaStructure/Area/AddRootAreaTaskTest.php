<?php

namespace Company\Domain\Task\InCompany\AreaStructure\Area;

use Company\Domain\Model\AreaStructure\AreaData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddRootAreaTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAreaDependency();
        $this->prepareAreaStructureDependency();
        $this->task = new AddRootAreaTask($this->areaRepository, $this->areaStructureRepository);
        //
        $this->payload = (new AreaData($this->createLabelData()))
                ->setAreaStructureId($this->areaStructureId);
    }
    
    //
    protected function executeInCompany()
    {
        $this->areaRepository->expects($this->any())
                ->method('isAreaRootNameAvailable')
                ->willReturn(true);
        $this->areaRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->areaId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_executeInCompany_addAreaCreatedInAreaStructureToRepository()
    {
        $this->areaStructure->expects($this->once())
                ->method('createRootArea')
                ->with($this->payload)
                ->willReturn($this->area);
        $this->areaRepository->expects($this->once())
                ->method('add')
                ->with($this->area);
        $this->executeInCompany();
    }
    public function test_executeInCompany_setPayloadId()
    {
        $this->executeInCompany();
        $this->assertSame($this->areaId, $this->payload->id);
    }
    public function test_executeInCompany_nameUnavailable_conflict()
    {
        $this->areaRepository->expects($this->once())
                ->method('isAreaRootNameAvailable')
                ->with($this->payload->labelData->name)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->executeInCompany(), 'Conflict', 'area name is unavailable');
    }
    public function test_execute_assertAreaStructureActive()
    {
        $this->areaStructure->expects($this->once())
                ->method('assertActive');
        $this->executeInCompany();
    }
}
