<?php

namespace Manager\Domain\Task\Sales;

use Manager\Domain\Model\Personnel\Manager\Sales;

interface SalesRepository
{

    public function ofId(string $id): Sales;

    public function aSalesManagedByManager(string $managerId, string $id): array;

    public function salesListManagedByManager(string $managerId, array $paginationSchema): array;
}
