<?php

namespace Tests\src\Company\Domain\Task\InCompany;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Task\InCompany\AreaStructure\Area\AreaRepository;
use Company\Domain\Task\InCompany\AreaStructure\AreaStructureRepository;
use Company\Domain\Task\InCompany\Personnel\PersonnelRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestBase;

class TaskInCompanyTestBase extends TestBase
{
    protected MockObject $personnelRepository;
    protected MockObject $personnel;
    protected string $personnelId = 'personnelId';
    
    protected function preparePersonnelDependency(): void
    {
        $this->personnelRepository = $this->buildMockOfInterface(PersonnelRepository::class);
        $this->personnel = $this->buildMockOfClass(Personnel::class);
    }
    
    protected MockObject $areaStructureRepository;
    protected MockObject $areaStructure;
    protected string $areaStructureId = 'areaStructureId';
    protected function prepareAreaStructureDependency(): void
    {
        $this->areaStructureRepository = $this->buildMockOfInterface(AreaStructureRepository::class);
        $this->areaStructure = $this->buildMockOfClass(AreaStructure::class);
        //
        $this->areaStructureRepository->expects($this->any())
                ->method('ofId')
                ->with($this->areaStructureId)
                ->willReturn($this->areaStructure);
    }
    
    protected MockObject $areaRepository;
    protected MockObject $area;
    protected string $areaId = 'areaId';
    protected function prepareAreaDependency(): void
    {
        $this->areaRepository = $this->buildMockOfInterface(AreaRepository::class);
        $this->area = $this->buildMockOfClass(Area::class);
        //
        $this->areaRepository->expects($this->any())
                ->method('ofId')
                ->with($this->areaId)
                ->willReturn($this->area);
    }
}
