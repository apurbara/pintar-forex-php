<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManager;
use Sales\Domain\DependencyModel\GreeterMetric;
use Sales\Domain\Task\GreetingMetricAchievement\GreetingMetricAchievementRepository;

class DoctrineGreetingMetricAchievementRepository implements GreetingMetricAchievementRepository
{

    public function __construct(protected EntityManager $em)
    {
        
    }

    public function greetingMetricAchievementOfSales(string $salesId, GreeterMetric $greeterMetric): ?array
    {
        return $greeterMetric->fetchSummaryResult($this->em->getConnection(), $salesId);
    }
}
