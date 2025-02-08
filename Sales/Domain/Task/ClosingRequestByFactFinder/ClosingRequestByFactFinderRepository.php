<?php

namespace Sales\Domain\Task\ClosingRequestByFactFinder;

use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinder;

interface ClosingRequestByFactFinderRepository
{

    public function nextIdentity(): string;

    public function add(ClosingRequestByFactFinder $closingRequestByFactFinder): void;

    public function ofId(string $id): ClosingRequestByFactFinder;

    public function closingRequestByFactFinderListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function aClosingRequestByFactFinderBelongsToSales(string $salesId, string $id): array;
}
