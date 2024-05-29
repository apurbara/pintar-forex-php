<?php

namespace Sales\Domain\Task\BySales\RecycleRequest;

use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequestData;
use Tests\src\Sales\Domain\Task\BySales\SalesTaskTestBase;

class UpdateRecycleRequestTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareRecycleRequestDependency();
        //
        $this->task = new UpdateRecycleRequestTask($this->recycleRequestRepository);
        $this->payload = (new RecycleRequestData('new note'))->setId($this->recycleRequestId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateRecycleRequest()
    {
        $this->recycleRequest->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
    public function test_execute_assertRecycleRequestBelongsToSales()
    {
        $this->recycleRequest->expects($this->once())
                ->method('assertManageableBySales')
                ->with($this->sales);
        $this->execute();
    }
}
