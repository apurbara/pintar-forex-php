<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\DependencyModel\CommonSalesMetric;

interface CommonSalesMetricRepository
{
    /**
     * 
     * @return CommonSalesMetric[]
     */
    public function allActive(): array;
}
