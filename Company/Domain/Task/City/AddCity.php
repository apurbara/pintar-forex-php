<?php

namespace Company\Domain\Task\City;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Province\City;
use Company\Domain\Model\Province\CityData;
use Company\Domain\Task\Province\ProvinceRepository;

class AddCity implements AdminTaskInCompany
{

    public function __construct(protected CityRepository $repository, protected ProvinceRepository $provinceRepository)
    {
        
    }

    /**
     * 
     * @param CityData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setId($this->repository->nextIdentity());
        
        $province = $this->provinceRepository->ofId($payload->provinceId);
        $city = new City($province, $payload->id, $payload);
        
        $this->repository->add($city);
    }
}
