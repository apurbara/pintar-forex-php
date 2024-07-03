<?php

namespace Manager\Domain\Task\RecycleRequest;

use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequestData;
use Resources\Event\Dispatcher;
use Tests\Manager\Domain\Task\ManagerTaskTestBase;


class ApproveRecycleRequestTest extends ManagerTaskTestBase
{
    protected $dispatcher;
    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareRecycleRequestDependency();
        $this->dispatcher = $this->buildMockOfClass(Dispatcher::class);
        //
        $this->task = new ApproveRecycleRequest($this->recycleRequestRepository, $this->dispatcher);
        $this->payload = (new RecycleRequestData())
                ->setId($this->recycleRequestId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->payload);
    }
    public function test_execute_approveRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('approve')
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
    public function test_execute_dispatchRecycleRequest()
    {
        $this->dispatcher->expects($this->once())
                ->method('dispatchEventContainer')
                ->with($this->recycleRequest);
        $this->execute();
    }
}
