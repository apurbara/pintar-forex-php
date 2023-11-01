<?php

namespace Sales\Domain\Task\RecycleRequest;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequest;

interface RecycleRequestRepository
{

    public function nextIdentity(): string;

    public function add(RecycleRequest $recycleRequest): void;

    public function ofId(string $id): RecycleRequest;

    public function recycleRequestListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function aRecycleRequestBelongsToSales(string $salesId, string $id): array;
}
