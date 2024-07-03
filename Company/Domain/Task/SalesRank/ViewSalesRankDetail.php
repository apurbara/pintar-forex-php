<?php

namespace Company\Domain\Task\SalesRank;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewSalesRankDetail implements AdminTaskInCompany
{

    public function __construct(protected SalesRankRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->aSalesRank($payload->id);
        $payload->setResult($result);
    }
}
