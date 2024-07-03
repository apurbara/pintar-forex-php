<?php

namespace Company\Domain\Task\Sales;

use Company\Domain\Model\Manager\Sales;

interface SalesRepository
{

    public function nextIdentity(): string;

    public function isEmailAvailable(string $email): bool;

    public function add(Sales $sales): void;

    public function ofId(string $id): Sales;

    //
    public function aSales(string $id);

    public function salesList(array $paginationSchema);

    public function allSales(array $searchSchema);
}
