<?php

namespace Sales\Domain\Task\GreetingAssignment;

use Tests\Sales\Domain\Task\SalesTaskTestBase;

class RecycleCustomerTest extends SalesTaskTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareGreetingAssignmentDependency();
        //
        $this->task = new RecycleCustomer($this->greetingAssignmentRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->greetingAssignmentId);
    }
    public function test_execute_validateCustomer()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('recycleCustomer');
        $this->execute();
    }
    public function test_execute_assertAssignmentBelongsToSales()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('assertBelongsToSales');
        $this->execute();
    }
}
