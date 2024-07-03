<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequestData;
use Tests\Manager\Domain\Task\ManagerTaskTestBase;

class RejectRecycleRequestTest extends ManagerTaskTestBase
{
    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareRecycleRequestDependency();
        //
        $this->task = new RejectRecycleRequest($this->recycleRequestRepository);
        $this->payload = (new RecycleRequestData())
                ->setId($this->recycleRequestId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->payload);
    }
    public function test_execute_rejectRecycleRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('reject')
                ->with($this->payload);
        $this->execute();
    }
    public function test_execute_assertRecycleRequestBelongsToManager()
    {
        $this->recycleRequest->expects($this->once())
                ->method('assertBelongsToManager')
                ->with($this->manager);
        $this->execute();
    }
}
