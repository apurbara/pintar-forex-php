<?php

namespace Company\Application\EventHandler;

use Company\Domain\Model\Manager\Sales\StrikingAssignment;

interface StrikingAssignmentRepository
{

    public function nextIdentity(): string;

    public function add(StrikingAssignment $strikingAssignment): void;
    
    public function update(): void;
}
