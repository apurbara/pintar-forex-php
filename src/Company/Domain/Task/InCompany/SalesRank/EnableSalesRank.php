<?php

namespace Company\Domain\Task\InCompany\SalesRank;

use Company\Domain\Model\AdminTaskInCompany;

class EnableSalesRank implements AdminTaskInCompany
{

    public function __construct(protected SalesRankRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload SalesRankId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->enable();
    }
}
