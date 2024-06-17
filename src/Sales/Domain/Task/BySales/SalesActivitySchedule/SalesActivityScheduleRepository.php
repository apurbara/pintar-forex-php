<?php

namespace Sales\Domain\Task\BySales\SalesActivitySchedule;

use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;

interface SalesActivityScheduleRepository
{

    public function nextIdentity(): string;

    public function add(SalesActivitySchedule $scheduledSalesActivity): void;

    public function ofId(string $id): SalesActivitySchedule;

    public function scheduledSalesActivityBelongsToSalesList(string $salesId, array $paginationSchema): array;

    public function scheduledSalesActivityBelongsToSalesDetail(string $salesId, string $id): array;

    public function totalSalesActivityScheduleBelongsToSales(string $salesId, array $searchSchema): int;

    public function salesActivityScheduleSummaryBelongsToSales(string $salesId, array $searchSchema): array;
    
    public function allNonInitialSchedulesInMonthBelongsToSales(string $salesId, int $year, int $month);
}
