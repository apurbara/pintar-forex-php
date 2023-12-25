<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;

interface ClosingRequestRepository
{

    public function ofId(string $id): ClosingRequest;

    public function closingRequestListBelongToManager(string $managerId, array $paginationSchema): array;

    public function aClosingRequestBelongToManager(string $managerId, string $id): ?array;

    public function monthlyTotalClosing(string $managerId, array $searchSchema): array;

    public function monthlyClosingCount(string $managerId, array $searchSchema): array;
}
