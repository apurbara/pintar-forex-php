<?php

namespace Sales\Domain\Task\BySales\ClosingRequest;

use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequest;

interface ClosingRequestRepository
{

    public function nextIdentity(): string;

    public function add(ClosingRequest $closingRequest): void;

    public function ofId(string $id): ClosingRequest;

    public function closingRequestListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function aClosingRequestBelongsToSales(string $salesId, string $id): array;
}
