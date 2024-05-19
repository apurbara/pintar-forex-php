<?php

namespace Company\Domain\Task\InCompany\SalesActivity;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\SalesActivityData;

class UpdateSalesActivity implements AdminTaskInCompany
{
    public function __construct(protected SalesActivityRepository $repository)
    {
    }
    
    /**
     * 
     * @param SalesActivityData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
