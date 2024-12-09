<?php

namespace Sales\Domain\Task\FactFindingMetricAchievement;

use Sales\Domain\DependencyModel\FactFinderMetric;

interface FactFindingMetricAchievementRepository
{

    public function factFindingMetricAchievementOfSales(string $salesId, FactFinderMetric $factFinderMetric): ?array;
}
