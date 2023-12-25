<?php

namespace Manager\Domain\Task\Sales;

interface SalesRepository
{

    public function aSalesManagedByManager(string $managerId, string $id): array;

    public function salesListManagedByManager(string $managerId, array $paginationSchema): array;
    
    
}
