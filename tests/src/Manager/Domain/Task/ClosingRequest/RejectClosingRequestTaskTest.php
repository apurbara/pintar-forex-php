<?php

namespace Manager\Domain\Task\ClosingRequest;

use Tests\src\Manager\Domain\Task\ManagerTaskTestBase;

class RejectClosingRequestTaskTest extends ManagerTaskTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareClosingRequestDependency();
        //
        $this->task = new RejectClosingRequestTask($this->closingRequestRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->closingRequestId);
    }
    public function test_execute_rejectClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('reject');
        $this->execute();
    }
    public function test_execute_assertClosingRequestManageableByManager()
    {
        $this->closingRequest->expects($this->once())
                ->method('assertManageableByManager')
                ->with($this->manager);
        $this->execute();
    }
}
