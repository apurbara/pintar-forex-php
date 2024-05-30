<?php

namespace Company\Domain\Task\InCompany\Sales;

use Company\Domain\Model\AdminTaskInCompany;

class CancelSalesAssignment implements AdminTaskInCompany
{

    public function __construct(protected SalesRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload salesId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->cancel();
    }
}
