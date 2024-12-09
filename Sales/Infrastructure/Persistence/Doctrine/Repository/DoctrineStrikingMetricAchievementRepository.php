<?php

namespace Sales\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManager;
use Sales\Domain\DependencyModel\StrikerMetric;
use Sales\Domain\Task\StrikingMetricAchievement\StrikingMetricAchievementRepository;

class DoctrineStrikingMetricAchievementRepository implements StrikingMetricAchievementRepository
{

    public function __construct(protected EntityManager $em)
    {
        
    }

    public function strikingMetricAchievementOfSales(string $salesId, StrikerMetric $strikerMetric): ?array
    {
        return $strikerMetric->fetchSummaryResult($this->em->getConnection(), $salesId);
    }
}
