<?php

namespace Sales\Domain\Task\StrikingAssignment;

use Tests\Sales\Domain\Task\SalesTaskTestBase;

class UpdateJourneyTest extends SalesTaskTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareStrikingAssignmentDependency();
        $this->prepareCustomerJourneyDependency();
        
        $this->task = new UpdateJourney($this->strikingAssignmentRepository, $this->customerJourneyRepository);
        
        $this->payload = (new UpdateJourneyPayload())
                ->setId($this->strikingAssignmentId)
                ->setCustomerJourneyId($this->customerJourneyId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateAssignmentJourney()
    {
        $this->strikingAssignment->expects($this->once())
                ->method('updateJourney')
                ->with($this->customerJourney);
        $this->execute();
    }
    public function test_execute_assertAssignmentBelongsToSaless()
    {
        $this->strikingAssignment->expects($this->once())
                ->method('assertBelongsToSales');
        $this->execute();
    }
}
