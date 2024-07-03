<?php

namespace Manager\Domain\Task\CustomerAssignment;

use Manager\Domain\Model\Manager\Sales\CustomerAssignment;

interface CustomerAssignmentRepository
{

    public function nextIdentity(): string;

    public function add(CustomerAssignment $customerAssignment): void;

    public function ofId(string $id): CustomerAssignment;

    //
    public function customerAssignmentListBelongsToManager(string $managerId, array $paginationSchema): array;

    public function aCustomerAssignmentBelongsToManager(string $managerId, string $id): array;
    
    public function assignmentCountBelongsToManager(string $managerId, array $searchSchema): ?int;
}
