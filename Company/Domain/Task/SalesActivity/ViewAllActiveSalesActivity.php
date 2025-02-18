<?php

namespace Company\Domain\Task\SalesActivity;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllActiveSalesActivity implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
{
    
    public function __construct(protected SalesActivityRepository $repository)
    {
    }

    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->allActiveSalesActivity($payload->listSchema);
        $payload->setResult($result);
    }
}
