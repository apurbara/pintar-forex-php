<?php

namespace Company\Domain\Task\InCompany\SalesActivity;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\SalesActivity;
use Company\Domain\Model\SalesActivityData;

class AddSalesActivityTask implements AdminTaskInCompany
{
    public function __construct(protected SalesActivityRepository $salesActivityRepository)
    {
    }
    
    /**
     * 
     * @param SalesActivityData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setId($this->salesActivityRepository->nextIdentity());
        
        $salesActivity = new SalesActivity($payload);
        $this->salesActivityRepository->add($salesActivity);
    }
}
