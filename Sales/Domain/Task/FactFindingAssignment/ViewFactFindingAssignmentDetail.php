<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewFactFindingAssignmentDetail implements SalesTask
{

    public function __construct(protected FactFindingAssignmentRepository $factFindingAssignmentRepository)
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
        $payload->setResult($this->factFindingAssignmentRepository->aFactFindingAssignmentBelongsToSales($sales->getId(),
                        $payload->id));
    }
}
