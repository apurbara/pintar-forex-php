<?php

namespace Sales\Domain\Task\ClosingRequestByFactFinder;

use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinderData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class UpdateClosingRequestByFactFinderTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareClosingRequestByFactFinderDependency();
        //
        $this->task = new UpdateClosingRequestByFactFinderTask($this->closingRequestByFactFinderRepository);
        $this->payload = (new ClosingRequestByFactFinderData(50000000, 'new note'))->setId($this->closingRequestByFactFinderId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateClosingRequestByFactFinder()
    {
        $this->closingRequestByFactFinder->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
    public function test_execute_assertClosingRequestByFactFinderBelongsToSales()
    {
        $this->closingRequestByFactFinder->expects($this->once())
                ->method('assertManageableBySales')
                ->with($this->sales);
        $this->execute();
    }
}
