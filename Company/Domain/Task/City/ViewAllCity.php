<?php

namespace Company\Domain\Task\City;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllCity implements AdminTaskInCompany
{

    public function __construct(protected CityRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->allCity($payload->listSchema);
        $payload->setResult($result);
    }
}
