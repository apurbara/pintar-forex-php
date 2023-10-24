<?php

namespace Company\Domain\Model;

use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\ManagerData;
use DateTimeImmutable;
use SharedContext\Domain\ValueObject\AccountInfo;
use SharedContext\Domain\ValueObject\AccountInfoData;
use Tests\TestBase;

class PersonnelTest extends TestBase
{
    protected $personnel;
    //
    protected $accountInfoData;
    protected $id = 'newPersonnelId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountInfoData = new AccountInfoData('personnel name', 'personnel@email.org', 'password123');
        //
        $data = (new PersonnelData($this->accountInfoData))->setId('id');
        $this->personnel = new TestablePersonnel($data);
        //
    }

    //
    protected function createPersonnelData()
    {
        $personnelData = new PersonnelData($this->accountInfoData);
        $personnelData->setId($this->id);
        return $personnelData;
    }

    //
    protected function construct()
    {
        return new TestablePersonnel($this->createPersonnelData());
    }
    public function test_construct_setProperties()
    {
        $personnel = $this->construct();
        $this->assertSame($this->id, $personnel->id);
        $this->assertFalse($personnel->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($personnel->createdTime);
        $this->assertInstanceOf(AccountInfo::class, $personnel->accountInfo);
    }
    
    //
    protected function assertActive()
    {
        $this->personnel->assertActive();
    }
    public function test_assertActive_inactivePersonnel_forbidden()
    {
        $this->personnel->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive personnel');
    }
    public function test_assertActive_activePersonnel_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
    
    //
    protected function assignAsManager()
    {
        $managerData = (new ManagerData())->setId('managerId');
        return $this->personnel->assignAsManager($managerData);
    }
    public function test_assignAsManager_returnManager()
    {
        $this->assertInstanceOf(Manager::class, $this->assignAsManager());
    }
    public function test_assignAsManager_disabledManager_forbidden()
    {
        $this->personnel->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assignAsManager(), 'Forbidden', 'only active personnel allow to be assigned as manager');
    }
}

class TestablePersonnel extends Personnel
{

    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public AccountInfo $accountInfo;
}
