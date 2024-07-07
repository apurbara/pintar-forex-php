<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\CustomerAssignment;

interface CustomerAssignmentRepository
{

    public function nextIdentity(): string;

    public function add(CustomerAssignment $customerAssignment): void;

    public function ofId(string $id): CustomerAssignment;

    //
    public function customerAssignmentList(array $paginationSchema): array;

    public function aCustomerAssignment(string $id): array;

    public function assignmentCount(array $searchSchema): ?int;
}
