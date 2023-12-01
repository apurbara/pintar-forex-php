<?php

namespace Manager\Domain\Service;

use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\Personnel\Manager\Sales;

class CustomerAssignmentPriorityCalculatorService
{
    /**
     * 
     * @var Sales[]
     */
    protected array $salesList;
    
    public function __construct()
    {
        $this->salesList = [];
    }
    
    public function registerSales(Sales $sales): void
    {
        $this->salesList[] = $sales;
    }
    
    public function getTopPrioritySalesForCustomerAssignment(Customer $customer): ?Sales
    {
        $topPrioritySales = null;
        $currentPriorityPoint = INF;
        foreach ($this->salesList as $sales) {
            $salesPriority = $sales->countAssignmentPriorityWithCustomer($customer);
            if ($salesPriority < $currentPriorityPoint){
                $topPrioritySales = $sales;
                $currentPriorityPoint = $salesPriority;
            }
        }
        return $topPrioritySales;
    }
}
