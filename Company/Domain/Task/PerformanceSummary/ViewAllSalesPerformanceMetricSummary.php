<?php

namespace Company\Domain\Task\PerformanceSummary;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Task\SalesPerformanceMetric\SalesPerformanceMetricRepository;
use Resources\Domain\TaskPayload\ViewPayload;

class ViewAllSalesPerformanceMetricSummary implements AdminTaskInCompany
{

    public function __construct(
            protected PerformanceSummaryRepository $repository,
            protected SalesPerformanceMetricRepository $salesPerformanceMetricRepository)
    {
        
    }

    /**
     * 
     * @param ViewPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = [];
        foreach ($this->salesPerformanceMetricRepository->allActive() as $salesPerformanceMetric) {
            $result[] = $this->repository->summaryOfSalesPerformanceMetric($salesPerformanceMetric);
        }
        $payload->setResult($result);
    }
}
