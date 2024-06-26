<?php

namespace Company\Domain\Task\InCompany\City;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Province\CityData;
use Company\Domain\Task\InCompany\Province\ProvinceRepository;

class UpdateCity implements AdminTaskInCompany
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
        $province = $this->provinceRepository->ofId($payload->provinceId);
        $this->repository->ofId($payload->id)
                ->update($province, $payload);
    }
}
