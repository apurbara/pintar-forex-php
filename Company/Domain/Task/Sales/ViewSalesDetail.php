<?php

namespace Company\Domain\Task\Sales;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewSalesDetail implements AdminTaskInCompany, ManagerTaskInCompany
{

    public function __construct(protected SalesRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->aSales($payload->id);
        $payload->setResult($result);
    }
}
