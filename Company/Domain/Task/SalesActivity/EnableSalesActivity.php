<?php

namespace Company\Domain\Task\SalesActivity;

use Company\Domain\Model\AdminTaskInCompany;

class EnableSalesActivity implements AdminTaskInCompany
{
    public function __construct(protected SalesActivityRepository $repository)
    {
    }
    
    /**
     * 
     * @param string $payload salesActivityId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->enable();
    }
}
