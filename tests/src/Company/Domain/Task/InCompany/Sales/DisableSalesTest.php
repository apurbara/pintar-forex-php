<?php

namespace Company\Domain\Task\InCompany\Sales;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class DisableSalesTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesDependency();
        //
        $this->task = new DisableSales($this->salesRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->salesId);
    }
    public function test_execute_disableSales()
    {
        $this->sales->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
