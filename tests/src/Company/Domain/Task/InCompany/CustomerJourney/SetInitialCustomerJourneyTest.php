<?php

namespace Company\Domain\Task\InCompany\CustomerJourney;

use Company\Domain\Model\CustomerJourneyData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class SetInitialCustomerJourneyTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerJourneyDependency();
        //
        $this->task = new SetInitialCustomerJourney($this->customerJourneyRepository);
        $this->payload = new CustomerJourneyData($this->createLabelData());
    }
    
    //
    protected function execute()
    {
        $this->customerJourneyRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->customerJourneyId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_addCustomerJourneyToRepository()
    {
        $this->customerJourneyRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_setPayloadAsInitial()
    {
        $this->execute();
        $this->assertTrue($this->payload->initial);
    }
    public function test_execute_alreadyContainInitialCustomerJourney_updateExistingInitialJourney()
    {
        $this->customerJourneyRepository->expects($this->once())
                ->method('anInitialCustomerJourney')
                ->willReturn($this->customerJourney);
        $this->customerJourney->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
    public function test_execute_alreadyContainInitialCustomerJourney_preventAddNewInitialJourney()
    {
        $this->customerJourneyRepository->expects($this->once())
                ->method('anInitialCustomerJourney')
                ->willReturn($this->customerJourney);
        $this->customerJourneyRepository->expects($this->never())
                ->method('add');
        $this->execute();
    }
}
