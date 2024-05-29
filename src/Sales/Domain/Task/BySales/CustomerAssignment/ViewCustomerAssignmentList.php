<?php

namespace Sales\Domain\Task\BySales\CustomerAssignment;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewCustomerAssignmentList implements SalesTask
{

    public function __construct(protected CustomerAssignmentRepository $customerAssignmentRepository)
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
        $payload->setResult($this->customerAssignmentRepository->customerAssignmentListBelongsToSales($sales->getId(),
                        $payload->paginationSchema));
    }
}
