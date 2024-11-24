<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;

interface GreetingAssignmentRepository extends ContainCustomerAssignmentRepository
{

    public function ofId(string $id): GreetingAssignment;

    //
    public function greetingAssignmentListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function aGreetingAssignmentBelongsToSales(string $salesId, string $id): array;

    public function totalGreetingAssignmentBelongsToSales(string $salesId, array $searchSchema): int;
}
