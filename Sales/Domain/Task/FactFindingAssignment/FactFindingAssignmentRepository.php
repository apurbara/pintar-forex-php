<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;

interface FactFindingAssignmentRepository extends ContainCustomerAssignmentRepository
{

    public function ofId(string $id): FactFindingAssignment;

    //
    public function factFindingAssignmentListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function aFactFindingAssignmentBelongsToSales(string $salesId, string $id): array;

    public function totalFactFindingAssignmentBelongsToSales(string $salesId, array $searchSchema): int;
}
