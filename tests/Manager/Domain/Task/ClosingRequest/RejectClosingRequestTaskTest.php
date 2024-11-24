<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequestData;
use Tests\Manager\Domain\Task\ManagerTaskTestBase;

class RejectClosingRequestTaskTest extends ManagerTaskTestBase
{
    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareClosingRequestDependency();
        //
        $this->task = new RejectClosingRequestTask($this->closingRequestRepository);
        $this->payload = (new ClosingRequestData())
                ->setId($this->closingRequestId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->payload);
    }
    public function test_execute_rejectClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('reject')
                ->with($this->payload);
        $this->execute();
    }
    public function test_execute_assertClosingRequestBelongsToManager()
    {
        $this->closingRequest->expects($this->once())
                ->method('assertBelongsToManager')
                ->with($this->manager);
        $this->execute();
    }
}
