<?php

namespace Manager\Domain\Task\PerformanceSummary;

use Manager\Domain\DependencyModel\CompanyMetric;
use Manager\Domain\DependencyModel\SalesPerformanceMetric;
use Manager\Domain\DependencyModel\SalesRank;

interface PerformanceSummaryRepository
{

    public function summaryOfSalesRankBelongsToManager(string $managerId, SalesRank $salesRank): ?array;

    public function summaryOfSalesPerformanceMetricBelongsToManager(string $managerId, SalesPerformanceMetric $salesPerformanceMetric): ?array;

    public function summaryOfCompanyMetricBelongsToManager(string $managerId, CompanyMetric $companyMetric): ?array;
}
