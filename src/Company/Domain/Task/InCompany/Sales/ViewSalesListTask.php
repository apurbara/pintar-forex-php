<?php

namespace Company\Domain\Task\InCompany\Sales;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewSalesListTask implements AdminTaskInCompany, PersonnelHavingManagerAssignmentTaskInCompany
{
    public function __construct(protected SalesRepository $salesRepository)
    {
    }
    
    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setResult($this->salesRepository->salesList($payload->paginationSchema));
    }
}
