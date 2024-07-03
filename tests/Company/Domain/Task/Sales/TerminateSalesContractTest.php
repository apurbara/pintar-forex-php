<?php

namespace Company\Domain\Task\Sales;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class TerminateSalesContractTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesDependency();
        //
        $this->task = new TerminateSalesContract($this->salesRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->salesId);
    }
    public function test_execute_cancelSales()
    {
        $this->sales->expects($this->once())
                ->method('terminateContract');
        $this->execute();
    }
}
