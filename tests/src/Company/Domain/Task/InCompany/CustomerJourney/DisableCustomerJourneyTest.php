<?php

namespace Company\Domain\Task\InCompany\CustomerJourney;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class DisableCustomerJourneyTest extends TaskInCompanyTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerJourneyDependency();
        //
        $this->task = new DisableCustomerJourney($this->customerJourneyRepository);
    }

    //
    protected function execute()
    {
        $this->task->executeInCompany($this->customerJourneyId);
    }
    public function test_execute_updateCustomerJourney()
    {
        $this->customerJourney->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
