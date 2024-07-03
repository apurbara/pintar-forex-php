<?php

namespace Company\Domain\Task\Sales;

use Company\Domain\Model\ManagerTaskInCompany;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllSales implements ManagerTaskInCompany
{
    
    public function __construct(protected SalesRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->allSales($payload->listSchema);
        $payload->setResult($result);
    }
}
