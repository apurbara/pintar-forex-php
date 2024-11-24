<?php

namespace Manager\Domain\Task\ClosingRequest;

use Manager\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequestData;
use Tests\Manager\Domain\Task\ManagerTaskTestBase;


class AcceptClosingRequestTaskTest extends ManagerTaskTestBase
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
        $this->task->executeByManager($this->manager, $this->payload);
    }
    public function test_execute_acceptClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('accept')
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
