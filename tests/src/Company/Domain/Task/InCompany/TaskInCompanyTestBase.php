<?php

namespace Tests\src\Company\Domain\Task\InCompany;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Model\SalesActivity;
use Company\Domain\Task\InCompany\AreaStructure\Area\AreaRepository;
use Company\Domain\Task\InCompany\AreaStructure\AreaStructureRepository;
use Company\Domain\Task\InCompany\CustomerJourney\CustomerJourneyRepository;
use Company\Domain\Task\InCompany\CustomerVerification\CustomerVerificationRepository;
use Company\Domain\Task\InCompany\Personnel\Manager\ManagerRepository;
use Company\Domain\Task\InCompany\Personnel\Manager\Sales\SalesRepository;
use Company\Domain\Task\InCompany\Personnel\PersonnelRepository;
use Company\Domain\Task\InCompany\SalesActivity\SalesActivityRepository;
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
    
    protected MockObject $customerVerificationRepository;
    protected MockObject $customerVerification;
    protected string $customerVerificationId = 'customerVerificationId';
    protected function prepareCustomerVerificationDependency(): void
    {
        $this->customerVerificationRepository = $this->buildMockOfInterface(CustomerVerificationRepository::class);
        $this->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        //
//        $this->customerVerificationRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->customerVerificationId)
//                ->willReturn($this->customerVerification);
    }
    
    protected MockObject $salesActivityRepository;
    protected MockObject $salesActivity;
    protected string $salesActivityId = 'salesActivityId';
    protected function prepareSalesActivityDependency(): void
    {
        $this->salesActivityRepository = $this->buildMockOfInterface(SalesActivityRepository::class);
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        //
//        $this->salesActivityRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->salesActivityId)
//                ->willReturn($this->salesActivity);
    }
    
    protected MockObject $customerJourneyRepository;
    protected MockObject $customerJourney;
    protected string $customerJourneyId = 'customerJourneyId';
    protected function prepareCustomerJourneyDependency(): void
    {
        $this->customerJourneyRepository = $this->buildMockOfInterface(CustomerJourneyRepository::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
//        $this->customerJourneyRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->customerJourneyId)
//                ->willReturn($this->customerJourney);
    }
}
