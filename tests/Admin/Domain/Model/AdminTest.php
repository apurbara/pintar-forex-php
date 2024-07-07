<?php

namespace Admin\Domain\Model;

use DateTimeImmutable;
use Shared\Domain\ValueObject\AccountInfo;
use Shared\Domain\ValueObject\ChangeUserPasswordData;
use Tests\TestBase;

class AdminTest extends TestBase
{
    protected $admin, $accountInfo;
    //
    protected $newAccountInfo;
    protected $changePasswordData;
    protected $name = 'new name';
    protected $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = new TestableAdmin();
        $this->accountInfo = $this->buildMockOfClass(AccountInfo::class);
        $this->admin->accountInfo = $this->accountInfo;
        //
        $this->newAccountInfo = $this->buildMockOfClass(AccountInfo::class);
        $this->changePasswordData = $this->buildMockOfReadonlyClass(ChangeUserPasswordData::class);
    }
    
    //
    protected function changePassword()
    {
        $this->admin->changePassword($this->changePasswordData);
    }
    public function test_changePassword_changeAccountInfoPassword()
    {
        $this->accountInfo->expects($this->once())
                ->method('changePassword')
                ->with($this->changePasswordData)
                ->willReturn($this->newAccountInfo);
        $this->changePassword();
        $this->assertSame($this->newAccountInfo, $this->admin->accountInfo);
    }
    
    //
    protected function changeName()
    {
        $this->admin->changeName($this->name);
    }
    public function test_changeName_changeAccountInfoPassword()
    {
        $this->accountInfo->expects($this->once())
                ->method('changeName')
                ->with($this->name)
                ->willReturn($this->newAccountInfo);
        $this->changeName();
        $this->assertSame($this->newAccountInfo, $this->admin->accountInfo);
    }
    
    //
    protected function login()
    {
        $this->accountInfo->expects($this->any())
                ->method('passwordMatch')
                ->willReturn(true);
        return $this->admin->login($this->password);
    }
    public function test_login_returnAdminId()
    {
        $this->assertSame($this->admin->id, $this->login());
    }
    public function test_login_disabledAdmin_forbidden()
    {
        $this->admin->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->login(), 'Unauthorized', 'inactive account or invalid email and password');
    }
    public function test_login_unmatchPassword_forbidden()
    {
        $this->accountInfo->expects($this->once())
                ->method('passwordMatch')
                ->with($this->password)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->login(), 'Unauthorized', 'inactive account or invalid email and password');
    }
}

class TestableAdmin extends Admin
{

    public string $id = 'adminId';
    public bool $disabled = false;
    public DateTimeImmutable $createdTime;
    public bool $aSuperUser;
    public AccountInfo $accountInfo;

    function __construct()
    {
        parent::__construct();
    }
}
