<?php

namespace Company\Domain\Task\Province;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Province;
use Company\Domain\Model\ProvinceData;

class AddProvince implements AdminTaskInCompany
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
        $payload->setId($this->repository->nextIdentity());
        $province = new Province($payload->id, $payload);
        $this->repository->add($province);
    }
}
