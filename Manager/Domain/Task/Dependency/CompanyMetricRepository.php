<?php

namespace Manager\Domain\Task\Dependency;

use Manager\Domain\DependencyModel\CompanyMetric;

interface CompanyMetricRepository
{

    /**
     * 
     * @return CompanyMetric[]
     */
    public function allActive(): array;
}
