<?php

namespace Company\Domain\Task\RecycleRequest;

interface RecycleRequestRepository
{

    public function recycleRequestList(array $paginationSchema): array;

    public function aRecycleRequest(string $id): ?array;

    public function monthlyRecycledCount(array $searchSchema): array;

    public function recycleRequestCount(array $searchSchema);
}
