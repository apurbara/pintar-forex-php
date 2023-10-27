<?php

namespace User\Domain\Task\ByPersonnel\Manager;

interface ManagerRepository
{

    public function activeManagerAssignmentBelongsToPersonnel(string $personnelId): ?array;
}
