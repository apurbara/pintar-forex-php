<?php

namespace Sales\Domain\Task\BySales\SalesActivityReport;

use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;

interface SalesActivityReportRepository
{

    public function nextIdentity(): string;

    public function add(SalesActivityReport $salesActivityReport): void;

    public function salesActivityReportListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function salesActivityReportDetailBelongsToSales(string $salesId, string $id): array;
}
