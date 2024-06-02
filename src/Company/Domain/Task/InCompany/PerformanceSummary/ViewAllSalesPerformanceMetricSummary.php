<?php

namespace Company\Domain\Task\InCompany\PerformanceSummary;

use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Task\InCompany\SalesPerformanceMetric\SalesPerformanceMetricRepository;
use Resources\Domain\TaskPayload\ViewPayload;

class ViewAllSalesPerformanceMetricSummary implements ManagerTaskInCompany
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
