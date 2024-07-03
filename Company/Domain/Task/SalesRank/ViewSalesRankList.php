<?php

namespace Company\Domain\Task\SalesRank;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewSalesRankList implements AdminTaskInCompany
{

    public function __construct(protected SalesRankRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->salesRankList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
