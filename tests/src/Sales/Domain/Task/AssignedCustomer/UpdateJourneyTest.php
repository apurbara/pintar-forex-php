<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomerData;
use Tests\src\Sales\Domain\Task\SalesTaskTestBase;

class UpdateJourneyTest extends SalesTaskTestBase
{

    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAssignedCustomerDependency();
        $this->prepareCustomerJourneyDependency();
        
        $this->task = new UpdateJourney($this->assignedCustomerRepository, $this->customerJourneyRepository);
        //
        $this->payload = (new AssignedCustomerData())
                ->setId($this->assignedCustomerId)
                ->setCustomerJourneyId($this->customerJourneyId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeBySales($this->sales, $this->payload);
    }
    public function test_execute_updateAssignedCustomerJourney()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('updateJourney')
                ->with($this->customerJourney);
        $this->execute();
    }
    public function test_execute_assertAssignedCustomerManageableBySales()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->execute();
    }
    
}
