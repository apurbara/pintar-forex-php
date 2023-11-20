<?php

namespace Manager\Domain\Task\RecycleRequest;

use Tests\src\Manager\Domain\Task\ManagerTaskTestBase;

class RejectRecycleRequestTest extends ManagerTaskTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareRecycleRequestDependency();
        //
        $this->task = new RejectRecycleRequest($this->recycleRequestRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->recycleRequestId);
    }
    public function test_execute_rejectRecycleRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('reject');
        $this->execute();
    }
    public function test_execute_assertRecycleRequestManageableByManager()
    {
        $this->recycleRequest->expects($this->once())
                ->method('assertManageableByManager')
                ->with($this->manager);
        $this->execute();
    }
}
