<?php

namespace Company\Domain\Task\SalesPerformanceMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewSalesPerformanceMetricDetail implements AdminTaskInCompany
{

    public function __construct(protected SalesPerformanceMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->aSalesPerfomanceMetric($payload->id);
        $payload->setResult($result);
    }
}
