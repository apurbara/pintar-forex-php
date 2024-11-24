<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\StrikingAssignment;

interface StrikingAssignmentRepository extends CustomerAssignmentRepository
{

    public function add(StrikingAssignment $strikingAssignment): void;

    public function ofId(string $id): StrikingAssignment;
}
