<?php

namespace Company\Domain\Task\SalesPerformanceMetric;

use Company\Domain\Model\SalesPerformanceMetric;

interface SalesPerformanceMetricRepository
{

    public function nextIdentity(): string;

    public function add(SalesPerformanceMetric $salesPerformanceMetric): void;

    public function ofId(string $id): SalesPerformanceMetric;

    /**
     * 
     * @return SalesPerformanceMetric[]
     */
    public function allActive(): array;

    //
    public function aSalesPerfomanceMetric(string $id);

    public function salesPerfomanceMetricList(array $paginationSchema);
}
