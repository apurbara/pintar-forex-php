<?php

namespace Sales\Domain\Task\StrikingMetricAchievement;

use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\StrikerMetricRepository;
use Sales\Domain\Task\SalesTask;

class ViewAllStrikingMetricAchievement implements SalesTask
{

    public function __construct(
            protected StrikingMetricAchievementRepository $repository, protected StrikerMetricRepository $strikerMetricRepository)
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
        foreach ($this->strikerMetricRepository->allActive() as $strikerMetric) {
            $result[] = $this->repository->strikingMetricAchievementOfSales($sales->getId(), $strikerMetric);
        }
        $payload->setResult($result);
    }
}
