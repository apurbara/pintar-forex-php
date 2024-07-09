<?php

namespace Company\Domain\Task\City;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewCityDetail implements AdminTaskInCompany
{

    public function __construct(protected CityRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->aCity($payload->id);
        $payload->setResult($result);
    }
}
