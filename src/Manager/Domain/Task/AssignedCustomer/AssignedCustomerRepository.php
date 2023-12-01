<?php

namespace Manager\Domain\Task\AssignedCustomer;

use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;

interface AssignedCustomerRepository
{

    public function nextIdentity(): string;

    public function add(AssignedCustomer $assignedCustomer): void;
}
