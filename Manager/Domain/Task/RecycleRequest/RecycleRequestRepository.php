<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;

interface RecycleRequestRepository
{

    public function ofId(string $id): RecycleRequest;

    //
    public function recycleRequestListBelongsToManager(string $managerId, array $paginationSchema): array;

    public function aRecycleRequestBelongsToManager(string $managerId, string $id): ?array;

    public function monthlyRecycledCountBelongsToManager(string $managerId, array $searchSchema): int;
    
    public function recycleRequestCountBelongsToManager(string $managerId, array $searchSchema): int;
}
