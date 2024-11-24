<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewFactFindingAssignmentList implements SalesTask
{

    public function __construct(protected FactFindingAssignmentRepository $factFindingAssignmentRepository)
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
        $payload->setResult($this->factFindingAssignmentRepository->factFindingAssignmentListBelongsToSales($sales->getId(),
                        $payload->paginationSchema));
    }
}
