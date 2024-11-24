<?php

namespace Sales\Domain\Task\FactFindingAssignment;

use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewTotalFactFindingAssignment implements SalesTask
{

    public function __construct(protected FactFindingAssignmentRepository $factFindingAssignmentRepository)
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
        $result = $this->factFindingAssignmentRepository->totalFactFindingAssignmentBelongsToSales($sales->getId(),
                $payload->searchSchema);
        $payload->setResult($result);
    }
}
