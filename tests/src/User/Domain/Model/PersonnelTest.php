<?php

namespace User\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\ValueObject\AccountInfo;
use Tests\TestBase;

class PersonnelTest extends TestBase
{
    protected $personnel, $accountInfo;
    //
    protected $password = 'password123';


    protected function setUp(): void
    {
        parent::setUp();
        $this->personnel = new TestablePersonnel();
        $this->accountInfo = $this->buildMockOfClass(AccountInfo::class);
        $this->personnel->accountInfo = $this->accountInfo;
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
