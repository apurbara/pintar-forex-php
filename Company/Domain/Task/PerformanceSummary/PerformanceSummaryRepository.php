<?php

namespace Company\Domain\Task\PerformanceSummary;

use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesRank;

interface PerformanceSummaryRepository
{

    public function summaryOfSalesRank(SalesRank $salesRank): ?array;

    public function summaryOfSalesPerformanceMetric(SalesPerformanceMetric $salesPerformanceMetric): ?array;

    public function summaryOfCompanyMetric(CompanyMetric $companyMetric): ?array;
}
