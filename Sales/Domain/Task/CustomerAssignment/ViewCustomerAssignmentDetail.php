<?php

namespace Sales\Domain\Task\CustomerAssignment;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewCustomerAssignmentDetail implements SalesTask
{

    public function __construct(protected CustomerAssignmentRepository $customerAssignmentRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setResult($this->customerAssignmentRepository->aCustomerAssignmentBelongsToSales($sales->getId(),
                        $payload->id));
    }
}
