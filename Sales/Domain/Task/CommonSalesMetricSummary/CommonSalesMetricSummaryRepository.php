<?php

namespace Sales\Domain\Task\CommonSalesMetricSummary;

use Sales\Domain\DependencyModel\CommonSalesMetric;

interface CommonSalesMetricSummaryRepository
{

    public function summaryOfCommonSalesMetricBelongsToSales(string $salesId, CommonSalesMetric $commonSalesMetric): ?array;
}
