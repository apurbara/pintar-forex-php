<?php

namespace Company\Domain\Task\InCompany\CustomerAssignment;

use Company\Domain\Model\Sales;

interface CustomerAssignmentDistributionServiceInterface
{

    public function registerSales(Sales $sales): void;

    public function getTopPrioritySales(): ?Sales;
}
