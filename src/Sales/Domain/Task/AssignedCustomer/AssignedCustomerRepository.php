<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

interface AssignedCustomerRepository
{

    public function nextIdentity(): string;

    public function add(AssignedCustomer $assignedCustomer): void;

    public function ofId(string $id): AssignedCustomer;
    
    public function assignedCustomerToSalesList(string $salesId, array $pageSchema): array;

    public function assignedCustomerToSalesDetail(string $salesId, string $id): array;
    
    public function totalCustomerAssignmentBelongsToSales(string $salesId, array $searchSchema): int;
}
