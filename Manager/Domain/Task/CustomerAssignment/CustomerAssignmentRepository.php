<?php

namespace Manager\Domain\Task\CustomerAssignment;

use Manager\Domain\Model\Manager\Sales\CustomerAssignment;

interface CustomerAssignmentRepository
{

    public function nextIdentity(): string;
    
    //
    public function customerAssignmentListBelongsByManager(string $managerId, array $paginationSchema): array;

    public function aCustomerAssignmentBelongsByManager(string $managerId, string $id): array;

    public function assignmentCountBelongsByManager(string $managerId, array $searchSchema): ?int;
}
