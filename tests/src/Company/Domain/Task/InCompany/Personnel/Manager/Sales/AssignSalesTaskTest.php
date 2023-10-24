<?php

namespace Company\Domain\Task\InCompany\Personnel\Manager\Sales;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AssignSalesTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesDependency();
        $this->prepareManagerDependency();
        $this->preparePersonnelDependency();
        $this->prepareAreaDependency();
        //
        $this->task = new AssignSalesTask($this->salesRepository, $this->managerRepository, $this->personnelRepository, $this->areaRepository);
        //
        $this->payload = (new \Company\Domain\Model\Personnel\Manager\SalesData('IN-HOUSE'))
                ->setManagerId($this->managerId)
                ->setPersonnelId($this->personnelId)
                ->setAreaId($this->areaId);
    }
    
    //
    protected function executeInCompany()
    {
        $this->salesRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->salesId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_addSalesAssignInManagerToRepository()
    {
        $this->manager->expects($this->once())
                ->method('assignPersonnelAsSales')
                ->with($this->personnel, $this->area, $this->payload)
                ->willReturn($this->sales);
        $this->salesRepository->expects($this->once())
                ->method('add')
                ->with($this->sales);
        $this->executeInCompany();
    }
    public function test_execute_assertManagerActive()
    {
        $this->manager->expects($this->once())
                ->method('assertActive');
        $this->executeInCompany();
    }
    public function test_execute_assertPersonnelActive()
    {
        $this->personnel->expects($this->once())
                ->method('assertActive');
        $this->executeInCompany();
    }
    public function test_execute_assertAreaActive()
    {
        $this->area->expects($this->once())
                ->method('assertActive');
        $this->executeInCompany();
    }
    public function test_execute_setPayloadId()
    {
        $this->executeInCompany();
        $this->assertSame($this->salesId, $this->payload->id);
    }
}
