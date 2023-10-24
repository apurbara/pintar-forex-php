<?php

namespace Company\Domain\Task\InCompany\Personnel\Manager\Sales;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Personnel\Manager\SalesData;
use Company\Domain\Task\InCompany\AreaStructure\Area\AreaRepository;
use Company\Domain\Task\InCompany\Personnel\Manager\ManagerRepository;
use Company\Domain\Task\InCompany\Personnel\PersonnelRepository;

class AssignSalesTask implements AdminTaskInCompany
{

    public function __construct(
            protected SalesRepository $salesRepository, protected ManagerRepository $managerRepository,
            protected PersonnelRepository $personnelRepository, protected AreaRepository $areaRepository)
    {
        
    }

    /**
     * 
     * @param SalesData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setId($this->salesRepository->nextIdentity());
        
        $personnel = $this->personnelRepository->ofId($payload->personnelId);
        $personnel->assertActive();
        
        $area = $this->areaRepository->ofId($payload->areaId);
        $area->assertActive();
        
        $manager = $this->managerRepository->ofId($payload->managerId);
        $manager->assertActive();
        
        $sales = $manager->assignPersonnelAsSales($personnel, $area, $payload);
        $this->salesRepository->add($sales);
    }
}
