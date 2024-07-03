<?php

namespace Company\Domain\Task\City;

use Company\Domain\Model\AdminTaskInCompany;

class EnableCity implements AdminTaskInCompany
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
                ->enable();
    }
}
