<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Tests\Sales\Domain\Task\SalesTaskTestBase;

class ValidateCustomerTest extends SalesTaskTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareGreetingAssignmentDependency();
        //
        $this->task = new ValidateCustomer($this->greetingAssignmentRepository, $this->dispatcher);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->greetingAssignmentId);
    }
    public function test_execute_validateCustomer()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('validateCustomer');
        $this->execute();
    }
    public function test_execute_assertAssignmentBelongsToSales()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('assertBelongsToSales');
        $this->execute();
    }
    public function test_execute_dispatchGreetingAssignment()
    {
        $this->dispatcher->expects($this->once())
                ->method('dispatchEventContainer')
                ->with($this->greetingAssignment);
        $this->execute();
    }
}
