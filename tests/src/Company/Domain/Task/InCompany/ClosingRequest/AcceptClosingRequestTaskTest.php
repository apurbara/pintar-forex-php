<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

use Company\Domain\Model\Personnel\Sales\CustomerAssignment\ClosingRequestData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AcceptClosingRequestTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareClosingRequestDependency();
        //
        $this->task = new AcceptClosingRequestTask($this->closingRequestRepository);
        $this->payload = (new ClosingRequestData())
                ->setId($this->closingRequestId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_acceptClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('accept');
        $this->execute();
    }
}
