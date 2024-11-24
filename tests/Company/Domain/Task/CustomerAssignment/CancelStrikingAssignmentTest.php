<?php

namespace Company\Domain\Task\CustomerAssignment;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;


class CancelStrikingAssignmentTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareStrikingAssignmentDependency();
        //
        $this->task = new CancelStrikingAssignment($this->strikingAssignmentRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->strikingAssignmentId);
    }
    public function test_execute_cancelCustomerAssignment()
    {
        $this->strikingAssignment->expects($this->once())
                ->method('cancel');
        $this->execute();
    }
}
