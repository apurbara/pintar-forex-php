<?php

namespace Manager\Domain\Task\CustomerAssignment;

use Tests\Manager\Domain\Task\ManagerTaskTestBase;

class CancelCustomerAssignmentTest extends ManagerTaskTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
        //
        $this->task = new CancelCustomerAssignment($this->customerAssignmentRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->customerAssignmentId);
    }
    public function test_execute_cancelCustomerAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('cancel');
        $this->execute();
    }
    public function test_execute_assertCustomerAssignmentBelongsToManager()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToManager')
                ->with($this->manager);
        $this->execute();
    }
}
