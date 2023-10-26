<?php

namespace Company\Domain\Task\InCompany\Personnel\Manager\Sales;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewSalesDetailTask implements AdminTaskInCompany, PersonnelTaskInCompany
{

    public function __construct(protected SalesRepository $salesRepository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->salesRepository->salesDetail($payload->id));
    }
}
