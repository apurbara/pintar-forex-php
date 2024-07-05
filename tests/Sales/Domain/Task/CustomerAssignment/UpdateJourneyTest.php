<?php

namespace Sales\Domain\Task\CustomerAssignment;

use Sales\Domain\Model\Sales\CustomerAssignmentData;
use Tests\Sales\Domain\Task\SalesTaskTestBase;

class UpdateJourneyTest extends SalesTaskTestBase
{

    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
        $this->prepareCustomerJourneyDependency();
        
        $this->task = new UpdateJourney($this->customerAssignmentRepository, $this->customerJourneyRepository);
        //
        $this->payload = (new CustomerAssignmentData())
                ->setId($this->customerAssignmentId)
                ->setCustomerJourneyId($this->customerJourneyId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateCustomerAssignmentJourney()
    {
        $this->customerAssignment->expects($this->once())
                ->method('updateJourney')
                ->with($this->customerJourney);
        $this->execute();
    }
    public function test_execute_assertCustomerAssignmentManageableBySales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    
}
