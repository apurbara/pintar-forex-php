<?php

namespace Company\Domain\Task\InCompany\Personnel\Manager;

use Company\Domain\Model\Personnel\ManagerData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AssignManagerTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->preparePersonnelDependency();
        $this->prepareManagerDependency();
        $this->task = new AssignManagerTask($this->managerRepository, $this->personnelRepository);
        //
        $this->payload = (new ManagerData())
                ->setPersonnelId($this->personnelId);
    }
    
    //
    protected function executeInCompany()
    {
        $this->managerRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->managerId);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_addManagerAssignedInPersonnelToRepository()
    {
        $this->personnel->expects($this->once())
                ->method('assignAsManager')
                ->with($this->payload)
                ->willReturn($this->manager);
        $this->managerRepository->expects($this->once())
                ->method('add')
                ->with($this->manager);
        $this->executeInCompany();
    }
    public function test_execute_setPayloadId()
    {
        $this->executeInCompany();
        $this->assertSame($this->managerId, $this->payload->id);
    }
}
