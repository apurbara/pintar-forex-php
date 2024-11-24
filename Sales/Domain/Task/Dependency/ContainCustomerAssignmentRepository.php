<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\Model\Sales\ContainCustomerAssignmentInterface;

interface ContainCustomerAssignmentRepository
{

    public function ofId(string $id): ContainCustomerAssignmentInterface;
}
