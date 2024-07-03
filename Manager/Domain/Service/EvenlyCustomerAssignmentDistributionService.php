<?php

namespace Manager\Domain\Service;

use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentDistributionServiceInterface;

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
