<?php

namespace Company\Domain\Task\InCompany\SalesActivity;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewSalesActivityDetail implements AdminTaskInCompany, PersonnelTaskInCompany
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
