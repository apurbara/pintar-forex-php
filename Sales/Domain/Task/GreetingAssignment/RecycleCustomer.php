<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class RecycleCustomer implements SalesTask
{

    public function __construct(protected GreetingAssignmentRepository $repository)
    {
        
    }

    public function executeBySales(Sales $sales, $payload): void
    {
        $greetingAssignment = $this->repository->ofId($payload);
        $greetingAssignment->assertBelongsToSales($sales);
        
        $greetingAssignment->recycleCustomer();
    }
}
