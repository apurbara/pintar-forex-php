<?php

namespace Sales\Application\Controllers;

use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\DependencyModel\StrikerMetric;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\StrikingMetricAchievement\ViewAllStrikingMetricAchievement;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineStrikingMetricAchievementRepository;

class StrikingMetricAchievementController extends BaseController
{

    public function viewAllStrikingMetricAchievement(Sales $sales)
    {
        $repository = new DoctrineStrikingMetricAchievementRepository($this->em);
        $strikerMetricRepository = $this->em->getRepository(StrikerMetric::class);
        
        $task = new ViewAllStrikingMetricAchievement($repository, $strikerMetricRepository);
        $payload = new ViewPayload();

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
