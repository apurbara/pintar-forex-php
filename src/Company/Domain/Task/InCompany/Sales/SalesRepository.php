<?php

namespace Company\Domain\Task\InCompany\Sales;

use Company\Domain\Model\Personnel\Sales;

interface SalesRepository
{

    public function nextIdentity(): string;

    public function add(Sales $sales): void;

    public function ofId(string $id): Sales;

    //
    public function salesList(array $paginationSchema): array;

    public function salesDetail(string $id): array;
}
