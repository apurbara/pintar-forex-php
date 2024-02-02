<?php

namespace User\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\ValueObject\AccountInfo;
use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use Tests\TestBase;
use User\Domain\Task\ByPersonnel\PersonnelTask;

class PersonnelTest extends TestBase
{
    protected $personnel, $accountInfo;
    //
    protected $password = 'password123';
    protected $task, $payload = 'string represent task payload';
    protected $newName = 'new name', $changeUserPasswordData;
    protected $updatedAccountInfo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->personnel = new TestablePersonnel();
        $this->accountInfo = $this->buildMockOfClass(AccountInfo::class);
        $this->personnel->accountInfo = $this->accountInfo;
        
        $this->updatedAccountInfo = $this->buildMockOfClass(AccountInfo::class);
        $this->changeUserPasswordData = new ChangeUserPasswordData('password123', 'newPassword123');
        //
        $this->task = $this->buildMockOfInterface(PersonnelTask::class);
        //
    }
    
    //
    protected function login()
    {
        $this->accountInfo->expects($this->any())
                ->method('passwordMatch')
                ->willReturn(true);
        return $this->personnel->login($this->password);
    }
    public function test_login_returnPersonnelId()
    {
        $this->assertSame($this->personnel->id, $this->login());
    }
    public function test_login_accountInfoPasswordDoesntMatch_unauthorized()
    {
        $this->accountInfo->expects($this->once())
                ->method('passwordMatch')
                ->with($this->password)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->login(), 'Unauthorized', 'inactive account or invalid email and password');
    }
    public function test_login_inactivePersonnel_unauthorized()
    {
        $this->personnel->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->login(), 'Unauthorized', 'inactive account or invalid email and password');
    }
    
    //
    protected function changeName()
    {
        $this->personnel->changeName($this->newName);
    }
    public function test_changeName_changeAccountInfoName()
    {
        $this->accountInfo->expects($this->once())
                ->method('changeName')
                ->with($this->newName)
                ->willReturn($this->updatedAccountInfo);
        $this->changeName();
        $this->assertSame($this->updatedAccountInfo, $this->personnel->accountInfo);
    }
    
    //
    protected function changePassword()
    {
        $this->personnel->changePassword($this->changeUserPasswordData);
    }
    public function test_changePassword_changeAccountInfoPassword()
    {
        $this->accountInfo->expects($this->once())
                ->method('changePassword')
                ->with($this->changeUserPasswordData)
                ->willReturn($this->updatedAccountInfo);
        $this->changePassword();
        $this->assertSame($this->updatedAccountInfo, $this->personnel->accountInfo);
    }
    
    //
    protected function executeTask()
    {
        $this->personnel->executeTask($this->task, $this->payload);
    }
    public function test_executeTask_executeTask()
    {
        $this->task->expects($this->once())
                ->method('executeByPersonnel')
                ->with($this->personnel, $this->payload);
        $this->executeTask();
    }
    public function test_executeTask_disabledPersonnel_forbidden()
    {
        $this->personnel->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTask(), 'Forbidden', 'only active personnel can make this request');
    }
}

class TestablePersonnel extends Personnel
{
    public string $id = 'personnelId';
    public bool $disabled = false;
    public DateTimeImmutable $createdTime;
    public AccountInfo $accountInfo;
    
    function __construct()
    {
        parent::__construct();
    }
}
