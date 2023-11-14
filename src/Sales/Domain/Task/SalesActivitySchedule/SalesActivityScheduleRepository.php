<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;

interface SalesActivityScheduleRepository
{

    public function nextIdentity(): string;

    public function add(SalesActivitySchedule $scheduledSalesActivity): void;

    public function ofId(string $id): SalesActivitySchedule;

    public function scheduledSalesActivityBelongsToSalesList(string $salesId, array $paginationSchema): array;

    public function scheduledSalesActivityBelongsToSalesDetail(string $salesId, string $id): array;
    
    public function totalSalesActivityScheduleBelongsToSales(string $salesId, array $searchSchema): int;
}
