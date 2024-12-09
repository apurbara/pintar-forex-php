<?php

namespace Sales\Domain\Task\FactFindingMetricAchievement;

use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\FactFinderMetricRepository;
use Sales\Domain\Task\SalesTask;

class ViewAllFactFindingMetricAchievement implements SalesTask
{

    public function __construct(
            protected FactFindingMetricAchievementRepository $repository, protected FactFinderMetricRepository $factFinderMetricRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ViewPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $result = [];
        foreach ($this->factFinderMetricRepository->allActive() as $factFinderMetric) {
            $result[] = $this->repository->factFindingMetricAchievementOfSales($sales->getId(), $factFinderMetric);
        }
        $payload->setResult($result);
    }
}
