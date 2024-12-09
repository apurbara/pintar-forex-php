<?php

namespace Sales\Application\Controllers;

use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\DependencyModel\FactFinderMetric;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\FactFindingMetricAchievement\ViewAllFactFindingMetricAchievement;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineFactFindingMetricAchievementRepository;

class FactFindingMetricAchievementController extends BaseController
{

    public function viewAllFactFindingMetricAchievement(Sales $sales)
    {
        $repository = new DoctrineFactFindingMetricAchievementRepository($this->em);
        $greeterMetricRepository = $this->em->getRepository(FactFinderMetric::class);
        
        $task = new ViewAllFactFindingMetricAchievement($repository, $greeterMetricRepository);
        $payload = new ViewPayload();

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
