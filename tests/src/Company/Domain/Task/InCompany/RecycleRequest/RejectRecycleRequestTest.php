<?php

namespace Company\Domain\Task\InCompany\RecycleRequest;

use Company\Domain\Model\Personnel\Sales\CustomerAssignment\RecycleRequestData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class RejectRecycleRequestTest extends TaskInCompanyTestBase
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
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_rejectRecycleRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('reject')
                ->with($this->payload);
        $this->execute();
    }
}
