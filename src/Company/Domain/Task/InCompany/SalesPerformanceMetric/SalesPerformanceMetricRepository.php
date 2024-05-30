<?php

namespace Company\Domain\Task\InCompany\SalesPerformanceMetric;

use Company\Domain\Model\SalesPerformanceMetric;

interface SalesPerformanceMetricRepository
{

    /**
     * 
     * @return SalesPerformanceMetric[]
     */
    public function allActive(): array;
}
