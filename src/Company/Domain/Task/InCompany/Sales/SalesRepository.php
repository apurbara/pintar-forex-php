<?php

namespace Company\Domain\Task\InCompany\Sales;

use Company\Domain\Model\Sales;

interface SalesRepository
{

    public function nextIdentity(): string;

    public function isEmailAvailable(string $email): bool;

    public function add(Sales $sales): void;

    public function ofId(string $id): Sales;

    //
    public function aSales(string $id);

    public function salesList(array $paginationSchema);
}
