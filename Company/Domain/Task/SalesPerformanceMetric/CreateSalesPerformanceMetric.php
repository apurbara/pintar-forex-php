<?php

namespace Company\Domain\Task\SalesPerformanceMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesPerformanceMetricData;

class CreateSalesPerformanceMetric implements AdminTaskInCompany
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
        $payload->setId($this->repository->nextIdentity());
        $salesPerformanceMetric = new SalesPerformanceMetric($payload->id, $payload);
        $this->repository->add($salesPerformanceMetric);
    }
}
