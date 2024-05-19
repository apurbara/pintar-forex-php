<?php

namespace Company\Domain\Task\InCompany\Sales;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Personnel\Sales;
use Company\Domain\Model\Personnel\SalesData;
use Company\Domain\Task\InCompany\Area\AreaRepository;
use Company\Domain\Task\InCompany\Personnel\PersonnelRepository;

class AssignSalesTask implements AdminTaskInCompany
{

    public function __construct(
            protected SalesRepository $salesRepository,
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
        $area = $this->areaRepository->ofId($payload->areaId);
        $sales = new Sales($personnel, $area, $payload->id, $payload);
        
        $this->salesRepository->add($sales);
    }
}
