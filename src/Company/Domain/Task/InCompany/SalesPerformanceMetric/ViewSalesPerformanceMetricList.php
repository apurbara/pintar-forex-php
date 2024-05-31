<?php

namespace Company\Domain\Task\InCompany\SalesPerformanceMetric;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewSalesPerformanceMetricList implements AdminTaskInCompany
{

    public function __construct(protected SalesPerformanceMetricRepository $repository)
    {
        
    }

    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->salesPerfomanceMetricList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
