<?php

namespace Company\Domain\Task\InCompany\SalesPerformanceMetric;

use Company\Domain\Model\AdminTaskInCompany;

class DisableSalesPerformanceMetric implements AdminTaskInCompany
{

    public function __construct(protected SalesPerformanceMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param string $payload SalesPerformanceMetricId
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $this->repository->ofId($payload)
                ->disable();
    }
}
