<?php

namespace Manager\Domain\Task\FactFindingAssignment;

use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;

interface FactFindingAssignmentRepository extends CustomerAssignmentRepository
{

    public function add(FactFindingAssignment $factFindingAssignment): void;
}
