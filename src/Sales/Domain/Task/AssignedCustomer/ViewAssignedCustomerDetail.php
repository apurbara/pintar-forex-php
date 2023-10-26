<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

class ViewAssignedCustomerDetail implements SalesTask
{

    public function __construct(protected AssignedCustomerRepository $assignedCustomerRepository)
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
        $payload->setResult($this->assignedCustomerRepository->assignedCustomerToSalesDetail($sales->getId(), $payload->id));
    }
}
