<?php

namespace Sales\Domain\Task\BySales\RecycleRequest;

use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequestData;
use Tests\src\Sales\Domain\Task\BySales\SalesTaskTestBase;

class SubmitRecycleRequestTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareRecycleRequestDependency();
        //
        $this->task = new SubmitRecycleRequestTask($this->recycleRequestRepository, $this->customerAssignmentRepository);
        $this->payload = (new RecycleRequestData('new note'))
                ->setCustomerAssignmentId($this->customerAssignmentId);
    }
    
    //
    protected function execute()
    {
        $this->recycleRequestRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->recycleRequestId);
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addRecycleRequestSubmittedInCustomerAssignmentToRepository()
    {
        $this->customerAssignment->expects($this->once())
                ->method('submitRecycleRequest')
                ->with($this->payload)
                ->willReturn($this->recycleRequest);
        $this->recycleRequestRepository->expects($this->once())
                ->method('add')
                ->with($this->recycleRequest);
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
        $this->assertSame($this->recycleRequestId, $this->payload->id);
    }
}
