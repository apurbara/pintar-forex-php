<?php

namespace Tests\src\Company\Domain\Task\InCompany;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Task\InCompany\AreaStructure\Area\AreaRepository;
use Company\Domain\Task\InCompany\AreaStructure\AreaStructureRepository;
use Company\Domain\Task\InCompany\Personnel\Manager\ManagerRepository;
use Company\Domain\Task\InCompany\Personnel\Manager\Sales\SalesRepository;
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
        //
        $this->personnelRepository->expects($this->any())
                ->method('ofId')
                ->with($this->personnelId)
                ->willReturn($this->personnel);
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
    
    protected MockObject $managerRepository;
    protected MockObject $manager;
    protected string $managerId = 'managerId';
    protected function prepareManagerDependency(): void
    {
        $this->managerRepository = $this->buildMockOfInterface(ManagerRepository::class);
        $this->manager = $this->buildMockOfClass(Manager::class);
        //
        $this->managerRepository->expects($this->any())
                ->method('ofId')
                ->with($this->managerId)
                ->willReturn($this->manager);
    }
    
    protected MockObject $salesRepository;
    protected MockObject $sales;
    protected string $salesId = 'salesId';
    protected function prepareSalesDependency(): void
    {
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->sales = $this->buildMockOfClass(Sales::class);
        //
//        $this->salesRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->salesId)
//                ->willReturn($this->sales);
    }
}
