<?php

namespace Sales\Domain\Task\StrikingAssignment;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewStrikingAssignmentDetail implements SalesTask
{

    public function __construct(protected StrikingAssignmentRepository $strikingAssignmentRepository)
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
        $payload->setResult($this->strikingAssignmentRepository->aStrikingAssignmentBelongsToSales($sales->getId(),
                        $payload->id));
    }
}
