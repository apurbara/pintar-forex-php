<?php

namespace Sales\Domain\Task\ClosingRequest;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequestData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class UpdateClosingRequestTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareClosingRequestDependency();
        //
        $this->task = new UpdateClosingRequestTask($this->closingRequestRepository);
        $this->payload = (new ClosingRequestData(50000000, 'new note'))->setId($this->closingRequestId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateClosingRequest()
    {
        $this->closingRequest->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
    public function test_execute_assertClosingRequestBelongsToSales()
    {
        $this->closingRequest->expects($this->once())
                ->method('assertManageableBySales')
                ->with($this->sales);
        $this->execute();
    }
}
