<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\Sales\CustomerAssignment\RecycleRequest;

interface RecycleRequestRepository
{

    public function ofId(string $id): RecycleRequest;

    public function recycleRequestList(array $paginationSchema): array;

    public function aRecycleRequest(string $id): ?array;

    public function monthlyRecycledCount(array $searchSchema): array;
}
