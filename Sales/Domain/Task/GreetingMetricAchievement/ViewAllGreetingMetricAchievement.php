<?php

namespace Sales\Domain\Task\GreetingMetricAchievement;

use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\Dependency\GreeterMetricRepository;
use Sales\Domain\Task\SalesTask;

class ViewAllGreetingMetricAchievement implements SalesTask
{

    public function __construct(
            protected GreetingMetricAchievementRepository $repository, protected GreeterMetricRepository $greeterMetricRepository)
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
        foreach ($this->greeterMetricRepository->allActive() as $greeterMetric) {
            $result[] = $this->repository->greetingMetricAchievementOfSales($sales->getId(), $greeterMetric);
        }
        $payload->setResult($result);
    }
}
