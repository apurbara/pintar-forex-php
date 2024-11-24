<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ViewGreetingAssignmentDetail implements SalesTask
{

    public function __construct(protected GreetingAssignmentRepository $greetingAssignmentRepository)
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
        $payload->setResult($this->greetingAssignmentRepository->aGreetingAssignmentBelongsToSales($sales->getId(),
                        $payload->id));
    }
}
