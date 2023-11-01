<?php

namespace Sales\Domain\Task\ClosingRequest;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequestData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class SubmitClosingRequestTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAssignedCustomerDependency();
        $this->prepareClosingRequestDependency();
        //
        $this->task = new SubmitClosingRequestTask($this->closingRequestRepository, $this->assignedCustomerRepository);
        $this->payload = (new ClosingRequestData('35000000', 'new note'))
                ->setAssignedCustomerId($this->assignedCustomerId);
    }
    
    //
    protected function execute()
    {
        $this->closingRequestRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->closingRequestId);
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addClosingRequestSubmittedInAssignedCustomerToRepository()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('submitClosingRequest')
                ->with($this->payload)
                ->willReturn($this->closingRequest);
        $this->closingRequestRepository->expects($this->once())
                ->method('add')
                ->with($this->closingRequest);
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
        $this->assertSame($this->closingRequestId, $this->payload->id);
    }
}
