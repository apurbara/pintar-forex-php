<?php

namespace Company\Domain\Task\CustomerAssignment;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;


class CancelCustomerAssignmentTest extends TaskInCompanyTestBase
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
        $this->customerAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerAssignmentId)
                ->willReturn($this->customerAssignment);
        $this->task->executeInCompany($this->customerAssignmentId);
    }
    public function test_execute_cancelCustomerAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('cancel');
        $this->execute();
    }
}
