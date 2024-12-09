<?php

namespace Sales\Domain\Task\GreetingMetricAchievement;

use Sales\Domain\DependencyModel\GreeterMetric;

interface GreetingMetricAchievementRepository
{

    public function greetingMetricAchievementOfSales(string $salesId, GreeterMetric $greeterMetric): ?array;
}
