<?php

namespace Manager\Domain\Task\GreetingAssignment;

use Manager\Domain\Model\Manager\Sales\GreetingAssignment;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;

interface GreetingAssignmentRepository extends CustomerAssignmentRepository
{

    public function add(GreetingAssignment $greetingAssignment): void;
}
