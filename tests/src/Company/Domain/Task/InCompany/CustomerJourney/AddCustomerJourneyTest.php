<?php

namespace Company\Domain\Task\InCompany\CustomerJourney;

use Company\Domain\Model\CustomerJourneyData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddCustomerJourneyTest extends TaskInCompanyTestBase
{

    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerJourneyDependency();
        $this->task = new AddCustomerJourney($this->customerJourneyRepository);
        $this->payload = new CustomerJourneyData($this->createLabelData());
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_addCustomerJourneyToRepository()
    {
        $this->customerJourneyRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_setPayloadI()
    {
        $this->customerJourneyRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->customerJourneyId);
        $this->execute();
        $this->assertSame($this->customerJourneyId, $this->payload->id);
    }
}
