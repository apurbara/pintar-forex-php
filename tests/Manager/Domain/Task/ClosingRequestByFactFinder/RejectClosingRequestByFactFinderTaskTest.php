<?php

namespace Manager\Domain\Task\ClosingRequestByFactFinder;

use Manager\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinderData;
use Tests\Manager\Domain\Task\ManagerTaskTestBase;

class RejectClosingRequestByFactFinderTaskTest extends ManagerTaskTestBase
{
    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareClosingRequestByFactFinderDependency();
        //
        $this->task = new RejectClosingRequestByFactFinderTask($this->closingRequestByFactFinderRepository);
        $this->payload = (new ClosingRequestByFactFinderData())
                ->setId($this->closingRequestByFactFinderId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeByManager($this->manager, $this->payload);
    }
    public function test_execute_rejectClosingRequestByFactFinder()
    {
        $this->closingRequestByFactFinder->expects($this->once())
                ->method('reject')
                ->with($this->payload);
        $this->execute();
    }
    public function test_execute_assertClosingRequestByFactFinderBelongsToManager()
    {
        $this->closingRequestByFactFinder->expects($this->once())
                ->method('assertBelongsToManager')
                ->with($this->manager);
        $this->execute();
    }
}
