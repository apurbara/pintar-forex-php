<?php

namespace Company\Domain\Task\InCompany\ClosingRequest;

class RejectClosingRequestTaskTest extends \Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase
{
    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareClosingRequestDependency();
        //
        $this->task = new RejectClosingRequestTask($this->closingRequestRepository);
        $this->payload = (new \Company\Domain\Model\Sales\CustomerAssignment\ClosingRequestData())
                ->setId($this->closingRequestId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_rejectClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('reject');
        $this->execute();
    }
}
