<?php

namespace Sales\Domain\Task\CustomerAssignment;

use Sales\Domain\Model\Sales\CustomerAssignment;

interface CustomerAssignmentRepository
{

    public function nextIdentity(): string;

    public function add(CustomerAssignment $customerAssignment): void;

    public function ofId(string $id): CustomerAssignment;
    
    public function update(): void;

    //
    public function customerAssignmentListBelongsToSales(string $salesId, array $pageSchema): array;

    public function aCustomerAssignmentBelongsToSales(string $salesId, string $id): array;

    public function totalCustomerAssignmentBelongsToSales(string $salesId, array $searchSchema): int;
}
