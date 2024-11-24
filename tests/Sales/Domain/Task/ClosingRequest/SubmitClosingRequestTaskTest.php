<?php

namespace Sales\Domain\Task\ClosingRequest;

use Sales\Domain\Model\Sales\StrikingAssignment\ClosingRequestData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class SubmitClosingRequestTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareStrikingAssignmentDependency();
        $this->prepareClosingRequestDependency();
        //
        $this->task = new SubmitClosingRequestTask($this->closingRequestRepository, $this->strikingAssignmentRepository);
        $this->payload = (new ClosingRequestData('35000000', 'new note'))
                ->setStrikingAssignmentId($this->strikingAssignmentId);
    }
    
    //
    protected function execute()
    {
        $this->closingRequestRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->closingRequestId);
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addClosingRequestSubmittedInStrikingAssignmentToRepository()
    {
        $this->closingRequestRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_assertStrikingAssignmentBelongsToSales()
    {
        $this->strikingAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->closingRequestId, $this->payload->id);
    }
}
