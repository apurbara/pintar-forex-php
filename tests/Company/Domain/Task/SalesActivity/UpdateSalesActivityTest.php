<?php

namespace Company\Domain\Task\SalesActivity;

use Company\Domain\Model\SalesActivityData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UpdateSalesActivityTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityDependency();
        //
        $this->task = new UpdateSalesActivity($this->salesActivityRepository);
        $this->payload = (new SalesActivityData($this->createLabelData(), 25))
                ->setId($this->salesActivityId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_disableSalesActivity()
    {
        $this->salesActivity->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
