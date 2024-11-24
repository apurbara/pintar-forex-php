<?php

namespace Manager\Domain\Task\StrikingAssignment;

use Manager\Domain\Model\Manager\Sales\StrikingAssignment;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;

interface StrikingAssignmentRepository extends CustomerAssignmentRepository
{

    public function add(StrikingAssignment $strikingAssignmnet): void;
}
