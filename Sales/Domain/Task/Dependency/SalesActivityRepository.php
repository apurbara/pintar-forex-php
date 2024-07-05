<?php

namespace Sales\Domain\Task\Dependency;

use Sales\Domain\DependencyModel\SalesActivity;

interface SalesActivityRepository
{

    public function ofId(string $id): SalesActivity;

    public function anInitialSalesActivity(): ?SalesActivity;
}
