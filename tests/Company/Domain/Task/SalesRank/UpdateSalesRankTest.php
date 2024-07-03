<?php

namespace Company\Domain\Task\SalesRank;

use Company\Domain\Model\SalesRankData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UpdateSalesRankTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesRankDependency();
        //
        $this->task = new UpdateSalesRank($this->salesRankRepository);
        $this->payload = (new SalesRankData())
                ->setId($this->salesRankId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateSalesRank()
    {
        $this->salesRank->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
