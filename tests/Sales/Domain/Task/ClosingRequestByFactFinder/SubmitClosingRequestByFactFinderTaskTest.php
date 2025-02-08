<?php

namespace Sales\Domain\Task\ClosingRequestByFactFinder;

use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinderData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class SubmitClosingRequestByFactFinderTaskTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFindingAssignmentDependency();
        $this->prepareClosingRequestByFactFinderDependency();
        //
        $this->task = new SubmitClosingRequestByFactFinderTask($this->closingRequestByFactFinderRepository, $this->factFindingAssignmentRepository);
        $this->payload = (new ClosingRequestByFactFinderData('35000000', 'new note'))
                ->setFactFindingAssignmentId($this->factFindingAssignmentId);
    }
    
    //
    protected function execute()
    {
        $this->closingRequestByFactFinderRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->closingRequestByFactFinderId);
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_addClosingRequestByFactFinderSubmittedInFactFindingAssignmentToRepository()
    {
        $this->closingRequestByFactFinderRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_assertFactFindingAssignmentBelongsToSales()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->closingRequestByFactFinderId, $this->payload->id);
    }
}
