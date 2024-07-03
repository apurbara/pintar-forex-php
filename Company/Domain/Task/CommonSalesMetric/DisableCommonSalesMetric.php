<?php

namespace Company\Domain\Task\CommonSalesMetric;

use Company\Domain\Model\AdminTaskInCompany;

class DisableCommonSalesMetric implements AdminTaskInCompany
{

    public function __construct(protected CommonSalesMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload CommonSalesMetricId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->disable();
    }
}
