<?php

namespace Company\Domain\Task\SalesActivity;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager\SalesTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewSalesActivityList implements AdminTaskInCompany, ManagerTaskInCompany, SalesTaskInCompany
{
    public function __construct(protected SalesActivityRepository $salesActivityRepository)
    {
    }
    
    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->salesActivityRepository->salesAcivityList($payload->paginationSchema));
    }
}
