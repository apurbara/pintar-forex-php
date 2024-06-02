<?php

namespace Company\Domain\Task\InCompany\Sales;

use Company\Domain\Model\SalesData;
use SharedContext\Domain\Enum\SalesType;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class CancelSalesTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesDependency();
        //
        $this->task = new CancelSales($this->salesRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->salesId);
    }
    public function test_execute_cancelSales()
    {
        $this->sales->expects($this->once())
                ->method('cancel');
        $this->execute();
    }
}
