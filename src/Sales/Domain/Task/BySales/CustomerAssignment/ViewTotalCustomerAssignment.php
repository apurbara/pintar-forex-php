<?php

namespace Sales\Domain\Task\BySales\CustomerAssignment;

use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewTotalCustomerAssignment implements SalesTask
{

    public function __construct(protected CustomerAssignmentRepository $customerAssignmentRepository)
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
        $result = $this->customerAssignmentRepository->totalCustomerAssignmentBelongsToSales($sales->getId(),
                $payload->searchSchema);
        $payload->setResult($result);
    }
}
