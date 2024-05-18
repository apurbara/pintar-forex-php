<?php

namespace Company\Domain\Task\InCompany\CustomerJourney;

use Company\Domain\Model\CustomerJourneyData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class UpdateCustomerJourneyTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerJourneyDependency();
        //
        $this->task = new UpdateCustomerJourney($this->customerJourneyRepository);
        $this->payload = (new CustomerJourneyData($this->createLabelData()))
                ->setId($this->customerJourneyId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateCustomerJourney()
    {
        $this->customerJourney->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
