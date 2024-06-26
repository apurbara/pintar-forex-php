<?php

namespace Company\Domain\Task\InCompany\City;

use Company\Domain\Model\AdminTaskInCompany;

class DisableCity implements AdminTaskInCompany
{

    public function __construct(protected CityRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload cityId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->disable();
    }
}
