<?php

namespace Manager\Domain\Service;

use Manager\Domain\Model\Personnel\Manager\Sales;

class CustomerAssignmentDistributionCalculatorService
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

    public function getTopPrioritySalesForCustomerDistribution(): ?Sales
    {
        $topPrioritySales = null;
        $currentPriorityPoint = INF;
        foreach ($this->salesList as $sales) {
            $salesPriority = $sales->countActiveAssignmentValue();
            if ($salesPriority < $currentPriorityPoint) {
                $topPrioritySales = $sales;
                $currentPriorityPoint = $salesPriority;
            }
        }
        return $topPrioritySales;
    }
}
