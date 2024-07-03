<?php

namespace Company\Domain\Task\SalesRank;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\SalesRankData;

class UpdateSalesRank implements AdminTaskInCompany
{

    public function __construct(protected SalesRankRepository $repository)
    {
        
    }

    /**
     * 
     * @param SalesRankData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
