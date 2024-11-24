<?php

namespace Company\Application\EventHandler;

use Company\Domain\Model\Manager\Sales\FactFindingAssignment;

interface FactFindingAssignmentRepository
{

    public function nextIdentity(): string;

    public function add(FactFindingAssignment $factFindingAssignment): void;

    public function ofId(string $id): FactFindingAssignment;
    
    public function update(): void;
}
