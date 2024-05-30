<?php

namespace Company\Domain\Task\InCompany\CompanyMetric;

use Company\Domain\Model\CompanyMetric;

interface CompanyMetricRepository
{

    /**
     * 
     * @return CompanyMetric[]
     */
    public function allActive(): array;
}
