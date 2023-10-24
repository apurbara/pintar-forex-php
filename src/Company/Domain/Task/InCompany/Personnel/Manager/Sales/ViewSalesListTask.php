<?php

namespace Company\Domain\Task\InCompany\Personnel\Manager\Sales;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewSalesListTask implements AdminTaskInCompany
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
