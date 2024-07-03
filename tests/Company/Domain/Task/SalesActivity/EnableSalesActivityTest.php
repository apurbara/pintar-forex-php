<?php

namespace Company\Domain\Task\SalesActivity;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class EnableSalesActivityTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesActivityDependency();
        //
        $this->task = new EnableSalesActivity($this->salesActivityRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->salesActivityId);
    }
    public function test_execute_disableSalesActivity()
    {
        $this->salesActivity->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
