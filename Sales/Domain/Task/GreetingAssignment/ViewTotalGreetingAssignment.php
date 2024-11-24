<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewTotalGreetingAssignment implements SalesTask
{

    public function __construct(protected GreetingAssignmentRepository $greetingAssignmentRepository)
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
        $result = $this->greetingAssignmentRepository->totalGreetingAssignmentBelongsToSales($sales->getId(),
                $payload->searchSchema);
        $payload->setResult($result);
    }
}
