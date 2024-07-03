<?php

namespace Company\Domain\Task\Sales;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\SalesData;
use Company\Domain\Task\City\CityRepository;
use Company\Domain\Task\Manager\ManagerRepository;
use Resources\Exception\RegularException;

class AddSales implements AdminTaskInCompany
{

    public function __construct(
            protected SalesRepository $repository, protected ManagerRepository $managerRepository,
            protected CityRepository $cityRepository)
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
        
        $manager = $this->managerRepository->ofId($payload->managerId);
        $city = isset($payload->cityId) ? $this->cityRepository->ofId($payload->cityId) : null;
        
        $sales = new Sales($manager, $city, $payload->id, $payload);
        $this->repository->add($sales);
    }
}
