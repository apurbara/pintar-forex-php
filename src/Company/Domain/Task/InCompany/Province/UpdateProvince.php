<?php

namespace Company\Domain\Task\InCompany\Province;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\ProvinceData;

class UpdateProvince implements AdminTaskInCompany
{
    public function __construct(protected ProvinceRepository $repository)
    {
    }
    
    /**
     * 
     * @param ProvinceData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
