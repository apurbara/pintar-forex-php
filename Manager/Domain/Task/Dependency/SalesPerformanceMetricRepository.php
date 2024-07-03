<?php

namespace Manager\Domain\Task\Dependency;

use Manager\Domain\DependencyModel\SalesPerformanceMetric;

interface SalesPerformanceMetricRepository
{
    /**
     * 
     * @return SalesPerformanceMetric[]
     */
    public function allActive(): array;
}
