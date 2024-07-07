<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\Manager\Sales;

interface CustomerAssignmentDistributionServiceInterface
{

    public function registerSales(Sales $sales): void;

    public function getTopPrioritySales(): ?Sales;
}
