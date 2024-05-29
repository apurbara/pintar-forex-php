<?php

namespace Company\Domain\Task\InCompany\CustomerAssignment;

use Company\Domain\Model\Personnel\Sales;

interface CustomerAssignmentDistributionServiceInterface
{

    public function registerSales(Sales $sales): void;

    public function getTopPrioritySales(): ?Sales;
}
