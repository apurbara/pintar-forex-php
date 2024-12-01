<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;

interface ClosingRequestRepository
{

    public function ofId(string $id): ClosingRequest;

    //
    public function closingRequestListBelongsToManager(string $managerId, array $paginationSchema): array;

    public function aClosingRequestBelongsToManager(string $managerId, string $id): ?array;

    public function closingRequestCountBelongsToManager(string $managerId, array $searchSchema);
}
