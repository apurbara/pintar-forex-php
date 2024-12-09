<?php

namespace Sales\Application\Controllers;

use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\DependencyModel\GreeterMetric;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\GreetingMetricAchievement\ViewAllGreetingMetricAchievement;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineGreetingMetricAchievementRepository;

class GreetingMetricAchievementController extends BaseController
{

    public function viewAllGreetingMetricAchievement(Sales $sales)
    {
        $repository = new DoctrineGreetingMetricAchievementRepository($this->em);
        $greeterMetricRepository = $this->em->getRepository(GreeterMetric::class);
        
        $task = new ViewAllGreetingMetricAchievement($repository, $greeterMetricRepository);
        $payload = new ViewPayload();

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
