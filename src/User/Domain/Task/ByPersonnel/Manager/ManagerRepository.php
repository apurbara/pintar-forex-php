<?php

namespace User\Domain\Task\ByPersonnel\Manager;

interface ManagerRepository
{

    public function managerAssignmentListBelongsToPersonnel(string $personnelId, array $paginationSchema): ?array;
}
