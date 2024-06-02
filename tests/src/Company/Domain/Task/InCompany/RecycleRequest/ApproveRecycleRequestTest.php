<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\Sales\CustomerAssignment\RecycleRequestData;
use Resources\Event\Dispatcher;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class ApproveRecycleRequestTest extends TaskInCompanyTestBase
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
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_approveRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('approve')
                ->with($this->payload);
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
