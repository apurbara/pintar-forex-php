<?php

namespace Company\Domain\Task\SalesRank;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\SalesRank;
use Company\Domain\Model\SalesRankData;

class CreateSalesRank implements AdminTaskInCompany
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
        $payload->setId($this->repository->nextIdentity());
        $salesRank = new SalesRank($payload->id, $payload);
        $this->repository->add($salesRank);
    }
}
