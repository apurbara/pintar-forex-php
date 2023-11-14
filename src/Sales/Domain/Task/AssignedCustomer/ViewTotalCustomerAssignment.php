<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

class ViewTotalCustomerAssignment implements SalesTask
{
    public function __construct(protected AssignedCustomerRepository $assignedCustomerRepository)
    {
    }
    
    /**
     * 
     * @param Sales $sales
     * @param ViewSummaryPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $result = $this->assignedCustomerRepository->totalCustomerAssignmentBelongsToSales($sales->getId(), $payload->searchSchema);
        $payload->setResult($result);
    }
}
