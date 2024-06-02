<?php

namespace Company\Domain\Service;

use Company\Domain\Model\Sales;
use Company\Domain\Task\InCompany\CustomerAssignment\CustomerAssignmentDistributionServiceInterface;

class LoadBalanceCustomerAssignmentDistributionService implements CustomerAssignmentDistributionServiceInterface
{

    /**
     * 
     * @var Sales[]
     */
    protected array $salesList;

    /**
     * 
     * @return Sales[]
     */
    public function getSalesList(): array
    {
        return $this->salesList;
    }

    public function __construct()
    {
        $this->salesList = [];
    }

    public function registerSales(Sales $sales): void
    {
        $this->salesList[] = $sales;
    }

    public function getTopPrioritySales(): ?Sales
    {
        $topPrioritySales = null;
        $currentLoadCount = INF;
        foreach ($this->salesList as $sales) {
            $salesLoadCount = $sales->calculateActiveCustomerAssignmentsCount();
            if ($salesLoadCount < $currentLoadCount) {
                $topPrioritySales = $sales;
                $currentLoadCount = $salesLoadCount;
            }
        }
        return $topPrioritySales;
    }
}
