<?php

namespace Sales\Domain\Task\RecycleRequest;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequestData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class SubmitRecycleRequestTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAssignedCustomerDependency();
        $this->prepareRecycleRequestDependency();
        //
        $this->task = new SubmitRecycleRequestTask($this->recycleRequestRepository, $this->assignedCustomerRepository);
        $this->payload = (new RecycleRequestData('new note'))
                ->setAssignedCustomerId($this->assignedCustomerId);
    }
    
    //
    protected function execute()
    {
        $this->recycleRequestRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->recycleRequestId);
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addRecycleRequestSubmittedInAssignedCustomerToRepository()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('submitRecycleRequest')
                ->with($this->payload)
                ->willReturn($this->recycleRequest);
        $this->recycleRequestRepository->expects($this->once())
                ->method('add')
                ->with($this->recycleRequest);
        $this->execute();
    }
    public function test_execute_assertAssignedCustomerBelongsToSales()
    {
        $this->assignedCustomer->expects($this->once())
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
