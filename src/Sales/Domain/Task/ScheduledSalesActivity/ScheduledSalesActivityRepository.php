<?php

namespace Sales\Domain\Task\ScheduledSalesActivity;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ScheduledSalesActivity;

interface ScheduledSalesActivityRepository
{

    public function nextIdentity(): string;

    public function add(ScheduledSalesActivity $scheduledSalesActivity): void;

    public function ofId(string $id): ScheduledSalesActivity;

    public function scheduledSalesActivityBelongsToSalesList(string $salesId, array $paginationSchema): array;

    public function scheduledSalesActivityBelongsToSalesDetail(string $salesId, string $id): array;
}
