<?php

namespace Company\Domain\Task\SalesActivity;

use Tests\TestBase;

class AddSalesActivityTaskTest extends \Tests\Company\Domain\Task\TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityDependency();
        $this->task = new AddSalesActivityTask($this->salesActivityRepository);
        //
        $this->payload = new \Company\Domain\Model\SalesActivityData($this->createLabelData(), 10);
    }
    
    //
    protected function execute()
    {
        $this->salesActivityRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->salesActivityId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_addSalesActivityToRepository()
    {
        $this->salesActivityRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->salesActivityId, $this->payload->id);
    }
}
