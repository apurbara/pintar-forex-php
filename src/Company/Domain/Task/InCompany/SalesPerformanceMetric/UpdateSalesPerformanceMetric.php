<?php

namespace Company\Domain\Task\InCompany\SalesPerformanceMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\SalesPerformanceMetricData;

class UpdateSalesPerformanceMetric implements AdminTaskInCompany
{

    public function __construct(protected SalesPerformanceMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param SalesPerformanceMetricData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload->id)
                ->update($payload);
    }
}
