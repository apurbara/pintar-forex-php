<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\GreetingAssignment;

interface GreetingAssignmentRepository extends CustomerAssignmentRepository
{

    public function add(GreetingAssignment $greetingAssignment): void;

    public function ofId(string $id): GreetingAssignment;
}
