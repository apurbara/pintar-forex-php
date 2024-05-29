<?php

namespace Sales\Domain\Task\BySales\RecycleRequest;

use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequest;

interface RecycleRequestRepository
{

    public function nextIdentity(): string;

    public function add(RecycleRequest $recycleRequest): void;

    public function ofId(string $id): RecycleRequest;

    public function recycleRequestListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function aRecycleRequestBelongsToSales(string $salesId, string $id): array;
}
