<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequestData;
use Manager\Domain\Task\RecycleRequest\ApproveRecycleRequest;
use Resources\Event\Dispatcher;
use Tests\src\Manager\Domain\Task\ManagerTaskTestBase;

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
    public function test_execute_assertClosingRequeestManageableByManager()
    {
        $this->recycleRequest->expects($this->once())
                ->method('assertManageableByManager')
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
