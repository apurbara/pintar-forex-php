<?php

namespace Company\Domain\Task\Sales;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\SalesData;
use Company\Domain\Task\City\CityRepository;
use Company\Domain\Task\Manager\ManagerRepository;

class UpdateSales implements AdminTaskInCompany
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
        $manager = $this->managerRepository->ofId($payload->managerId);
        $city = isset($payload->cityId) ? $this->cityRepository->ofId($payload->cityId) : null;
        $this->repository->ofId($payload->id)
                ->update($manager, $city, $payload);
    }
}
