<?php

namespace Company\Domain\Task\Province;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
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
