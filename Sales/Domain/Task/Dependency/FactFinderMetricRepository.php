<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\DependencyModel\FactFinderMetric;

interface FactFinderMetricRepository
{

    /**
     * 
     * @return FactFinderMetric[]
     */
    public function allActive(): array;
}
