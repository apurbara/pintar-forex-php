<?php

namespace Manager\Domain\Task\CustomerAssignment;

use Manager\Domain\Model\Manager\Sales;

interface CustomerAssignmentDistributionServiceInterface
{

    public function registerSales(Sales $sales): void;

    public function getTopPrioritySales(): ?Sales;
}
