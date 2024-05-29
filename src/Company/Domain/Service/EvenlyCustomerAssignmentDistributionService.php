<?php

namespace Company\Domain\Service;

use Company\Domain\Model\Personnel\Sales;
use Company\Domain\Task\InCompany\CustomerAssignment\CustomerAssignmentDistributionServiceInterface;

class EvenlyCustomerAssignmentDistributionService implements CustomerAssignmentDistributionServiceInterface
{

    protected array $salesList = [];

    public function registerSales(Sales $sales): void
    {
        $this->salesList[] = $sales;
    }

    protected int $key = 0;
    public function getTopPrioritySales(): ?Sales
    {
        $sales = $this->salesList[$this->key] ?? null;
        $this->key = $this->key < (count($this->salesList) - 1) ? $this->key + 1 : 0;
        return $sales;
    }
}
