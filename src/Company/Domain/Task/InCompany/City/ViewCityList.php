<?php

namespace Company\Domain\Task\InCompany\City;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Model\SalesTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewCityList implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
{

    public function __construct(protected CityRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->cityList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
