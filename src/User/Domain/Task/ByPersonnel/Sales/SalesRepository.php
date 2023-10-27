<?php

namespace User\Domain\Task\ByPersonnel\Sales;

interface SalesRepository
{

    public function activeSalesAssignmentBelongsToPersonnel(string $personnelId): ?array;
}
