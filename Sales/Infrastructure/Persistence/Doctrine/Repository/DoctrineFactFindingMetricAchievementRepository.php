<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManager;
use Sales\Domain\DependencyModel\FactFinderMetric;
use Sales\Domain\Task\FactFindingMetricAchievement\FactFindingMetricAchievementRepository;

class DoctrineFactFindingMetricAchievementRepository implements FactFindingMetricAchievementRepository
{

    public function __construct(protected EntityManager $em)
    {
        
    }

    public function factFindingMetricAchievementOfSales(string $salesId, FactFinderMetric $factFinderMetric): ?array
    {
        return $factFinderMetric->fetchSummaryResult($this->em->getConnection(), $salesId);
    }
}
