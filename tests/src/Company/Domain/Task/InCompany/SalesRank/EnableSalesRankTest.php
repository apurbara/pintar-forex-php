<?php

namespace Company\Domain\Task\InCompany\SalesRank;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class EnableSalesRankTest extends TaskInCompanyTestBase
{

    protected $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesRankDependency();
        //
        $this->task = new EnableSalesRank($this->salesRankRepository);
    }

    //
    protected function execute()
    {
        $this->task->executeInCompany($this->salesRankId);
    }
    public function test_execute_enableSalesRank()
    {
        $this->salesRank->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
