<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

class ViewAssignedCustomerList implements SalesTask
{

    public function __construct(protected AssignedCustomerRepository $assignedCustomerRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setResult($this->assignedCustomerRepository->assignedCustomerToSalesList($sales->getId(),
                        $payload->paginationSchema));
    }
}
