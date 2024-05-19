<?php

namespace Company\Domain\Task\InCompany\SalesActivity;

use Company\Domain\Model\AdminTaskInCompany;

class DisableSalesActivity implements AdminTaskInCompany
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
                ->disable();
    }
}
