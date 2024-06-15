<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

use Company\Domain\Model\Sales\CustomerAssignment\ClosingRequest;

interface ClosingRequestRepository
{

    public function ofId(string $id): ClosingRequest;

    public function closingRequestList(array $paginationSchema): array;

    public function aClosingRequest(string $id): ?array;

    public function monthlyTotalClosing(array $searchSchema): array;

    public function monthlyClosingCount(array $searchSchema): array;
    
    public function closingRequestCount(array $searchSchema);
}
