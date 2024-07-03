<?php

namespace Company\Domain\Task\SalesActivity;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewSalesActivityDetail implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
{

    public function __construct(protected SalesActivityRepository $salesActivityRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->salesActivityRepository->salesAcivityDetail($payload->id));
    }
}
