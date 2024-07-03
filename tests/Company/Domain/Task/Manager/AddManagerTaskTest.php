<?php

namespace Company\Domain\Task\Manager;

use Company\Domain\Model\ManagerData;
use SharedContext\Domain\ValueObject\AccountInfoData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class AddManagerTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $managerData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareManagerDependency();
        $this->task = new AddManagerTask($this->managerRepository);
        //
        $this->managerData = new ManagerData(new AccountInfoData('name', 'user@email.org', 'password123'));
    }
    //
    protected function executeInCompany()
    {
        $this->managerRepository->expects($this->any())
                ->method('isEmailAvailable')
                ->willReturn(true);
        $this->managerRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->managerId);
        $this->task->executeInCompany($this->managerData);
    }
    public function test_executeInCompany_addManagerToRepository()
    {
        $this->managerRepository->expects($this->once())
                ->method('add');
        $this->executeInCompany();
    }
    public function test_executeInCompany_setManagerDataId()
    {
        $this->executeInCompany();
        $this->assertSame($this->managerId, $this->managerData->id);
    }
    public function test_executeInCompany_emailAlreadyUsed()
    {
        $this->managerRepository->expects($this->once())
                ->method('isEmailAvailable')
                ->with($this->managerData->accountInfoData->email)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->executeInCompany(), 'Conflict', 'email already registered');
    }
}
