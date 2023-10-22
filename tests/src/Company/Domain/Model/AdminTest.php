<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\ValueObject\AccountInfo;
use SharedContext\Domain\ValueObject\AccountInfoData;
use Tests\TestBase;

class AdminTest extends TestBase
{
    protected $admin;
    protected $accountInfoData, $aSuperUser = true;
    protected $id = 'newAdminId';
    //
    protected $companyTask, $payload = 'string represent task payload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountInfoData = new AccountInfoData('admin name', 'admin@email.org', 'password123');
        
        $data = new AdminData($this->accountInfoData, true);
        $data->setId('id');
        $this->admin = new TestableAdmin($data);
        //
        $this->companyTask = $this->buildMockOfInterface(AdminTaskInCompany::class);
    }
    
    //
    protected function createAdminData()
    {
        $data = new AdminData($this->accountInfoData, $this->aSuperUser);
        $data->setId($this->id);
        return $data;
    }
    
    //
    protected function construct()
    {
        return new TestableAdmin($this->createAdminData());
    }
    public function test_construct_setProperties()
    {
        $admin = $this->construct();
        $this->assertSame($this->id, $admin->id);
        $this->assertFalse($admin->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($admin->createdTime);
        $this->assertSame($this->aSuperUser, $admin->aSuperUser);
        $this->assertInstanceOf(AccountInfo::class, $admin->accountInfo);
    }
    
    //
    protected function executeTaskInCompany()
    {
        $this->admin->executeTaskInCompany($this->companyTask, $this->payload);
    }
    public function test_executeTaskInCompany_executeTask()
    {
        $this->companyTask->expects($this->once())
                ->method('executeInCompany')
                ->with($this->payload);
        $this->executeTaskInCompany();
    }
    public function test_executeTaskInCompany_disabledAdmin_Forbidden()
    {
        $this->admin->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTaskInCompany(), 'Unauthorized', 'only active admin can make this request');
    }
}

class TestableAdmin extends Admin
{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public bool $aSuperUser;
    public AccountInfo $accountInfo;
}
