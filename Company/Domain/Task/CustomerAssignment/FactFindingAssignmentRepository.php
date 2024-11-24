<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\FactFindingAssignment;

interface FactFindingAssignmentRepository extends CustomerAssignmentRepository
{

    public function add(FactFindingAssignment $factFindingAssignment): void;

    public function ofId(string $id): FactFindingAssignment;
}
