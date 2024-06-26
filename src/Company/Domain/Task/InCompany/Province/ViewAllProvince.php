<?php

namespace Company\Domain\Task\InCompany\Province;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Model\SalesTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllProvince implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
{
    public function __construct(protected ProvinceRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->allProvince($payload->listSchema);
        $payload->setResult($result);
    }
}
