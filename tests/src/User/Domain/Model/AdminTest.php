<?php

namespace User\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\ValueObject\AccountInfo;
use Tests\TestBase;

class AdminTest extends TestBase
{
    protected $admin, $accountInfo;
    //
    protected $password = 'password123';


    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = new TestableAdmin();
        $this->accountInfo = $this->buildMockOfClass(AccountInfo::class);
        $this->admin->accountInfo = $this->accountInfo;
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
    public function test_login_accountInfoPasswordDoesntMatch_unauthorized()
    {
        $this->accountInfo->expects($this->once())
                ->method('passwordMatch')
                ->with($this->password)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->login(), 'Unauthorized', 'inactive account or invalid email and password');
    }
    public function test_login_inactiveAdmin_unauthorized()
    {
        $this->admin->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->login(), 'Unauthorized', 'inactive account or invalid email and password');
    }
}

class TestableAdmin extends Admin
{
    public string $id = 'adminId';
    public bool $disabled = false;
    public DateTimeImmutable $createdTime;
    public AccountInfo $accountInfo;
    
    function __construct()
    {
        parent::__construct();
    }
}
