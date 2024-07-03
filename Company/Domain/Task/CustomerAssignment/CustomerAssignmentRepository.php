<?php

namespace Company\Domain\Task\CustomerAssignment;

interface CustomerAssignmentRepository
{

    public function customerAssignmentList(array $paginationSchema): array;

    public function aCustomerAssignment(string $id): array;

    public function assignmentCount(array $searchSchema): ?int;
}
