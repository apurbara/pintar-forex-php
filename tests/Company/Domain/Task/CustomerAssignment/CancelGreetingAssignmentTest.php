<?php

namespace Company\Domain\Task\CustomerAssignment;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;


class CancelGreetingAssignmentTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareGreetingAssignmentDependency();
        //
        $this->task = new CancelGreetingAssignment($this->greetingAssignmentRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->greetingAssignmentId);
    }
    public function test_execute_cancelCustomerAssignment()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('cancel');
        $this->execute();
    }
}
