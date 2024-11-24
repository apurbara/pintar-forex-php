<?php

namespace Sales\Domain\Task\StrikingAssignment;

use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewTotalStrikingAssignment implements SalesTask
{

    public function __construct(protected StrikingAssignmentRepository $strikingAssignmentRepository)
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
        $result = $this->strikingAssignmentRepository->totalStrikingAssignmentBelongsToSales($sales->getId(),
                $payload->searchSchema);
        $payload->setResult($result);
    }
}
