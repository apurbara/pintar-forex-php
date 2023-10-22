<?php

namespace Company\Domain\Task\InCompany\Personnel;

use Company\Domain\Model\PersonnelData;
use SharedContext\Domain\ValueObject\AccountInfoData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddPersonnelTaskTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $personnelData;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->preparePersonnelDependency();
        $this->task = new AddPersonnelTask($this->personnelRepository);
        //
        $this->personnelData = new PersonnelData(new AccountInfoData('name', 'user@email.org', 'password123'));
    }
    //
    protected function executeInCompany()
    {
        $this->personnelRepository->expects($this->any())
                ->method('isEmailAvailable')
                ->willReturn(true);
        $this->personnelRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->personnelId);
        $this->task->executeInCompany($this->personnelData);
    }
    public function test_executeInCompany_addPersonnelToRepository()
    {
        $this->personnelRepository->expects($this->once())
                ->method('add');
        $this->executeInCompany();
    }
    public function test_executeInCompany_setPersonnelDataId()
    {
        $this->executeInCompany();
        $this->assertSame($this->personnelId, $this->personnelData->id);
    }
    public function test_executeInCompany_emailAlreadyUsed()
    {
        $this->personnelRepository->expects($this->once())
                ->method('isEmailAvailable')
                ->with($this->personnelData->accountInfoData->email)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->executeInCompany(), 'Conflict', 'email already registered');
    }
}
