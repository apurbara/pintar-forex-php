<?php

namespace Company\Domain\Task\InCompany\SalesRank;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class DisableSalesRankTest extends TaskInCompanyTestBase
{

    protected $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesRankDependency();
        //
        $this->task = new DisableSalesRank($this->salesRankRepository);
    }

    //
    protected function execute()
    {
        $this->task->executeInCompany($this->salesRankId);
    }
    public function test_execute_disableSalesRank()
    {
        $this->salesRank->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
