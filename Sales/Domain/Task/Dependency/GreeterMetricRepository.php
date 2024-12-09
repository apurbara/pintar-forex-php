<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\DependencyModel\GreeterMetric;

interface GreeterMetricRepository
{
    /**
     * 
     * @return GreeterMetric[]
     */
    public function allActive(): array;
}
