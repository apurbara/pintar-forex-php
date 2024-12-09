<?php

namespace Sales\Domain\Task\StrikingMetricAchievement;

use Sales\Domain\DependencyModel\StrikerMetric;

interface StrikingMetricAchievementRepository
{

    public function strikingMetricAchievementOfSales(string $salesId, StrikerMetric $strikerMetric): ?array;
}
