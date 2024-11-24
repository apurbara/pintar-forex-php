<?php

namespace Sales\Domain\Task\StrikingAssignment;

use Sales\Domain\Model\Sales\StrikingAssignment;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;

interface StrikingAssignmentRepository extends ContainCustomerAssignmentRepository
{

    public function ofId(string $id): StrikingAssignment;

    //
    public function strikingAssignmentListBelongsToSales(string $salesId, array $paginationSchema): array;

    public function aStrikingAssignmentBelongsToSales(string $salesId, string $id): array;

    public function totalStrikingAssignmentBelongsToSales(string $salesId, array $searchSchema): int;
}
