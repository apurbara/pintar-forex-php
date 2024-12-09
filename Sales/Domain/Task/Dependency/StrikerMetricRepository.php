<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\DependencyModel\StrikerMetric;

interface StrikerMetricRepository
{

    /**
     * 
     * @return StrikerMetric[]
     */
    public function allActive(): array;
}
