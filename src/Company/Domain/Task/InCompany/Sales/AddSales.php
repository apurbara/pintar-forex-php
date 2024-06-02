<?php

namespace Company\Domain\Task\InCompany\Sales;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Sales;
use Company\Domain\Model\SalesData;
use Company\Domain\Task\InCompany\Area\AreaRepository;
use Resources\Exception\RegularException;

class AddSales implements AdminTaskInCompany
{

    public function __construct(protected SalesRepository $repository, protected AreaRepository $areaRepository)
    {
        
    }

    /**
     * 
     * @param SalesData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setId($this->repository->nextIdentity());
        if (!$this->repository->isEmailAvailable($payload->accountInfoData->email)) {
            throw RegularException::conflict('email already registered');
        }
        
        $area = isset($payload->areaId) ? $this->areaRepository->ofId($payload->areaId) : null;
        $sales = new Sales($area, $payload->id, $payload);
        $this->repository->add($sales);
    }
}
