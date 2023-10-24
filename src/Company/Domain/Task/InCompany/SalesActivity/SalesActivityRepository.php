<?php

namespace Company\Domain\Task\InCompany\SalesActivity;

use Company\Domain\Model\SalesActivity;

interface SalesActivityRepository
{

    public function nextIdentity(): string;

    public function add(SalesActivity $salesActivity): void;

    public function anInitialSalesActivity(): ?SalesActivity;

    public function salesAcivityList(array $paginationSchema): array;

    public function salesAcivityDetail(string $id): array;
}
