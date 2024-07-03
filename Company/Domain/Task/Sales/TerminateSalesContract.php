<?php

namespace Company\Domain\Task\Sales;

use Company\Domain\Model\AdminTaskInCompany;

class TerminateSalesContract implements AdminTaskInCompany
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
                ->terminateContract();
    }
}
