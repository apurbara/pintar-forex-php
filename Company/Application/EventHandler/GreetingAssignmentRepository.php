<?php

namespace Company\Application\EventHandler;

use Company\Domain\Model\Manager\Sales\GreetingAssignment;

interface GreetingAssignmentRepository
{

    public function ofId(string $id): GreetingAssignment;
}
