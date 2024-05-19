<?php

namespace Company\Domain\Task\InCompany\Sales;

use Company\Domain\Model\Personnel\SalesData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AssignSalesTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesDependency();
        $this->preparePersonnelDependency();
        $this->prepareAreaDependency();
        //
        $this->task = new AssignSalesTask($this->salesRepository, $this->personnelRepository, $this->areaRepository);
        //
        $this->payload = (new SalesData('IN-HOUSE'))
                ->setPersonnelId($this->personnelId)
                ->setAreaId($this->areaId);
    }
    
    //
    protected function executeInCompany()
    {
        $this->salesRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->salesId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->executeInCompany();
        $this->assertSame($this->salesId, $this->payload->id);
    }
    public function test_execute_addSalesToRepository()
    {
        $this->salesRepository->expects($this->once())
                ->method('add');
        $this->executeInCompany();
    }
}
