<?php

namespace Sales\Domain\Task\ClosingRequest;

use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequestData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class SubmitClosingRequestTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareClosingRequestDependency();
        //
        $this->task = new SubmitClosingRequestTask($this->closingRequestRepository, $this->customerAssignmentRepository);
        $this->payload = (new ClosingRequestData('35000000', 'new note'))
                ->setCustomerAssignmentId($this->customerAssignmentId);
    }
    
    //
    protected function execute()
    {
        $this->closingRequestRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->closingRequestId);
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addClosingRequestSubmittedInCustomerAssignmentToRepository()
    {
        $this->customerAssignment->expects($this->once())
                ->method('submitClosingRequest')
                ->with($this->payload)
                ->willReturn($this->closingRequest);
        $this->closingRequestRepository->expects($this->once())
                ->method('add')
                ->with($this->closingRequest);
        $this->execute();
    }
    public function test_execute_assertCustomerAssignmentBelongsToSales()
    {
        $this->customerAssignment->expects($this->once())
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
