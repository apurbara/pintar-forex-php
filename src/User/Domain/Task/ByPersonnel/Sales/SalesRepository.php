<?php

namespace User\Domain\Task\ByPersonnel\Sales;

interface SalesRepository
{

    public function salesAssignmentListBelongsToPersonnel(string $personnelId, array $paginationSchema): ?array;
}
