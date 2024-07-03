<?php

namespace Manager\Domain\Task\PerformanceSummary;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\Dependency\SalesPerformanceMetricRepository;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewPayload;

class ViewAllSalesPerformanceMetricSummary implements ManagerTask
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
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = [];
        foreach ($this->salesPerformanceMetricRepository->allActive() as $salesPerformanceMetric) {
            $result[] = $this->repository
                    ->summaryOfSalesPerformanceMetricBelongsToManager($manager->getId(), $salesPerformanceMetric);
        }
        $payload->setResult($result);
    }
}
