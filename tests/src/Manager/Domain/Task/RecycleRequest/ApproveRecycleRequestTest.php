<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Task\RecycleRequest\ApproveRecycleRequest;
use Tests\src\Manager\Domain\Task\ManagerTaskTestBase;

class ApproveRecycleRequestTest extends ManagerTaskTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareRecycleRequestDependency();
        //
        $this->task = new ApproveRecycleRequest($this->recycleRequestRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->recycleRequestId);
    }
    public function test_execute_approveRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('approve');
        $this->execute();
    }
    public function test_execute_assertClosingRequeestManageableByManager()
    {
        $this->recycleRequest->expects($this->once())
                ->method('assertManageableByManager')
                ->with($this->manager);
        $this->execute();
    }
}
