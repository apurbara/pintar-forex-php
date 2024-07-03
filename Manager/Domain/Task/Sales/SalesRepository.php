<?php

namespace Manager\Domain\Task\Sales;

use Manager\Domain\Model\Manager\Sales;

interface SalesRepository
{

    public function ofId(string $id): Sales;

    //
    public function salesListBelongsToManager(string $managerId, array $paginationSchema);

    public function aSalesBelongsToManager(string $managerId, string $id);
    
    public function allSalesBelongsToManager(string $managerId, array $searchSchema);
}
