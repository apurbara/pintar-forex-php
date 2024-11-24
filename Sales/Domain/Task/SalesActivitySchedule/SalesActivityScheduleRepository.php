<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;

interface SalesActivityScheduleRepository
{

    public function nextIdentity(): string;

    public function add(SalesActivitySchedule $scheduledSalesActivity): void;
    
    public function ofId(string $id): SalesActivitySchedule;

    //
    public function salesActivityScheduleListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function aSalesActivityScheduleBelongsToSales(string $salesId, string $id): array;

    public function totalSalesActivityScheduleBelongsToSales(string $salesId, array $searchSchema): int;
    
    public function allOngoingSalesActivityScheduleBelongsToSales(string $salesId, array $searchSchema): array;
}
