<?php

namespace Company\Domain\Model;

use Company\Domain\Task\TaskInCompany;
use DateTimeImmutable;
use Shared\Domain\ValueObject\AccountInfo;
use Shared\Domain\ValueObject\AccountInfoData;
use Tests\TestBase;

class ManagerTest extends TestBase
{
    protected $manager;
    //
    protected $accountInfoData;
    protected $id = 'newManagerId';
    //
    protected $task, $payload = 'string represent task payload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountInfoData = new AccountInfoData('manager name', 'manager@email.org', 'password123');
        //
        $data = (new ManagerData($this->accountInfoData))->setId('id');
        $this->manager = new TestableManager('id',$data);
        //
        $this->task = $this->buildMockOfInterface(ManagerTaskInCompany::class);
    }

    //
    protected function createManagerData()
    {
        return new ManagerData($this->accountInfoData);
    }

    //
    protected function construct()
    {
        return new TestableManager($this->id, $this->createManagerData());
    }
    public function test_construct_setProperties()
    {
        $manager = $this->construct();
        $this->assertSame($this->id, $manager->id);
        $this->assertFalse($manager->suspended);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($manager->createdTime);
        $this->assertInstanceOf(AccountInfo::class, $manager->accountInfo);
    }
    
    //
    protected function suspend()
    {
        $this->manager->suspend();
    }
    public function test_suspend_setSuspended()
    {
        $this->suspend();
        $this->assertTrue($this->manager->suspended);
    }
    
    //
    protected function unsuspend()
    {
        $this->manager->unsuspend();
    }
    public function test_unsuspend_setSuspendedFalse()
    {
        $this->manager->suspended = true;
        $this->unsuspend();
        $this->assertFalse($this->manager->suspended);
    }
    
    //
    protected function assertActive()
    {
        $this->manager->assertActive();
    }
    public function test_assertActive_suspendedManager_forbidden()
    {
        $this->manager->suspended = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive manager');
    }
    public function test_assertActive_activeManager_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
    
    //
    protected function executeTaskInCompany()
    {
        $this->manager->executeTaskInCompany($this->task, $this->payload);
    }
    public function test_executeTaskInCompany_executeTask()
    {
        $this->task->expects($this->once())
                ->method('executeInCompany')
                ->with($this->payload);
        $this->executeTaskInCompany();
    }
    public function test_executeTaskInCompany_suspendedManager_forbidden()
    {
        $this->manager->suspended = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTaskInCompany(), 'Forbidden', 'only active manager can  make this request');
    }
    public function test_executeTaskInCompany_notManagerTask_typeError()
    {
        $this->task = $this->buildMockOfClass(TaskInCompany::class);
        $this->expectException(\TypeError::class);
        $this->executeTaskInCompany();
    }
    
}

class TestableManager extends Manager
{

    public string $id;
    public bool $suspended;
    public DateTimeImmutable $createdTime;
    public AccountInfo $accountInfo;
}
