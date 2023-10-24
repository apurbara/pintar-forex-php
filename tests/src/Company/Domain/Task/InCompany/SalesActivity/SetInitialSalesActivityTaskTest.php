<?php

namespace Company\Domain\Task\InCompany\SalesActivity;

use Company\Domain\Model\SalesActivityData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class SetInitialSalesActivityTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityDependency();
        $this->task = new SetInitialSalesActivityTask($this->salesActivityRepository);
        //
        $this->payload = new SalesActivityData($this->createLabelData(), 10);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_addSalesActivityToRepository()
    {
        $this->salesActivityRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_alreadyHasInitialSalesActivity_updateExistingInitialSalesActivity()
    {
        $this->salesActivityRepository->expects($this->once())
                ->method('anInitialSalesActivity')
                ->willReturn($this->salesActivity);
        $this->salesActivity->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
    public function test_execute_alreadyHasInitialSalesActivity_preventAddNewSalesActivity()
    {
        $this->salesActivityRepository->expects($this->once())
                ->method('anInitialSalesActivity')
                ->willReturn($this->salesActivity);
        $this->salesActivityRepository->expects($this->never())
                ->method('add');
        $this->execute();
    }
}
