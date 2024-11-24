<?php

namespace Company\Domain\Task\CustomerAssignment;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;


class CancelFactFindingAssignmentTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFindingAssignmentDependency();
        //
        $this->task = new CancelFactFindingAssignment($this->factFindingAssignmentRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->factFindingAssignmentId);
    }
    public function test_execute_cancelCustomerAssignment()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('cancel');
        $this->execute();
    }
}
