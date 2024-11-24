<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewGreetingAssignmentList implements SalesTask
{

    public function __construct(protected GreetingAssignmentRepository $greetingAssignmentRepository)
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
        $payload->setResult($this->greetingAssignmentRepository->greetingAssignmentListBelongsToSales($sales->getId(),
                        $payload->paginationSchema));
    }
}
