<?php

namespace Sales\Domain\Task\SalesActivityReport;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;

interface SalesActivityReportRepository
{

    public function nextIdentity(): string;

    public function add(SalesActivityReport $salesActivityReport): void;

    public function salesActivityReportListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function salesActivityReportDetailBelongsToSales(string $salesId, string $id): array;
}
