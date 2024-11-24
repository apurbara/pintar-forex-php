<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Resources\Event\Dispatcher;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class ValidateCustomer implements SalesTask
{

    public function __construct(protected GreetingAssignmentRepository $repository, protected Dispatcher $dispatcher)
    {
        
    }

    public function executeBySales(Sales $sales, $payload): void
    {
        $greetingAssignment = $this->repository->ofId($payload);

        $greetingAssignment->assertBelongsToSales($sales);
        $greetingAssignment->validateCustomer();
        
        $this->dispatcher->dispatchEventContainer($greetingAssignment);
    }
}
