<?php

namespace Company\Domain\Task\Province;

use Company\Domain\Model\AdminTaskInCompany;

class DisableProvince implements AdminTaskInCompany
{
    public function __construct(protected ProvinceRepository $repository)
    {
    }
    
    /**
     * 
     * @param string $payload provinceId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->disable();
    }
}
