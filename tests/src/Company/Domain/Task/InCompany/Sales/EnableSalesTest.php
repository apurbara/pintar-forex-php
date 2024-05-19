<?php

namespace Company\Domain\Task\InCompany\Sales;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class EnableSalesTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesDependency();
        //
        $this->task = new EnableSales($this->salesRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->salesId);
    }
    public function test_execute_enableSales()
    {
        $this->sales->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
