<?php

namespace Company\Domain\Task\CustomerJourney;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class EnableCustomerJourneyTest extends TaskInCompanyTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerJourneyDependency();
        //
        $this->task = new EnableCustomerJourney($this->customerJourneyRepository);
    }

    //
    protected function execute()
    {
        $this->task->executeInCompany($this->customerJourneyId);
    }
    public function test_execute_updateCustomerJourney()
    {
        $this->customerJourney->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
