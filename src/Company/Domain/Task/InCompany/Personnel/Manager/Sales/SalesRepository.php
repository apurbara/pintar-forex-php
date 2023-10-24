<?php

namespace Company\Domain\Task\InCompany\Personnel\Manager\Sales;

use Company\Domain\Model\Personnel\Manager\Sales;

interface SalesRepository
{

    public function nextIdentity(): string;

    public function add(Sales $sales): void;

    public function salesList(array $paginationSchema): array;

    public function salesDetail(string $id): array;
}
