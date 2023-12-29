<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;

interface RecycleRequestRepository
{

    public function ofId(string $id): RecycleRequest;

    public function recycleRequestListBelongToManager(string $managerId, array $paginationSchema): array;

    public function aRecycleRequestBelongToManager(string $managerId, string $id): ?array;

    public function monthlyRecycledCount(string $managerId, array $searchSchema): array;
}
