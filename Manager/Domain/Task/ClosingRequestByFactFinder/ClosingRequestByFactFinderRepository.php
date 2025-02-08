<?php

namespace Manager\Domain\Task\ClosingRequestByFactFinder;

use Manager\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinder;

interface ClosingRequestByFactFinderRepository
{

    public function ofId(string $id): ClosingRequestByFactFinder;

    //
    public function closingRequestByFactFinderListBelongsToManager(string $managerId, array $paginationSchema): array;

    public function aClosingRequestByFactFinderBelongsToManager(string $managerId, string $id): ?array;

    public function closingRequestByFactFinderCountBelongsToManager(string $managerId, array $searchSchema);
}
