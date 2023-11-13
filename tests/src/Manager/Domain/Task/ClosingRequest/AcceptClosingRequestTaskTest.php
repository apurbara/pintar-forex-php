<?php

namespace Manager\Domain\Task\ClosingRequest;

use Tests\src\Manager\Domain\Task\ManagerTaskTestBase;

class AcceptClosingRequestTaskTest extends ManagerTaskTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareClosingRequestDependency();
        //
        $this->task = new AcceptClosingRequestTask($this->closingRequestRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->closingRequestId);
    }
    public function test_execute_acceptClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('accept');
        $this->execute();
    }
    public function test_execute_assertClosingRequeestManageableByManager()
    {
        $this->closingRequest->expects($this->once())
                ->method('assertManageableByManager')
                ->with($this->manager);
        $this->execute();
    }
}
