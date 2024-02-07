<?php

namespace Company\Domain\Model;

use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\ManagerData;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SharedContext\Domain\ValueObject\AccountInfo;
use SharedContext\Domain\ValueObject\AccountInfoData;
use Tests\TestBase;

class PersonnelTest extends TestBase
{
    protected $personnel;
    protected $manager;
    //
    protected $accountInfoData;
    protected $id = 'newPersonnelId';
    //
    protected $task, $payload = 'string represent task payload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountInfoData = new AccountInfoData('personnel name', 'personnel@email.org', 'password123');
        //
        $data = (new PersonnelData($this->accountInfoData))->setId('id');
        $this->personnel = new TestablePersonnel($data);
        
        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->personnel->managerAssignments = new ArrayCollection();
        $this->personnel->managerAssignments->add($this->manager);
        //
        $this->task = $this->buildMockOfInterface(PersonnelTaskInCompany::class);
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
    protected function executeTaskInCompany()
    {
        $this->personnel->executeTaskInCompany($this->task, $this->payload);
    }
    public function test_executeTaskInCompany_executeTask()
    {
        $this->task->expects($this->once())
                ->method('executeInCompany')
                ->with($this->payload);
        $this->executeTaskInCompany();
    }
    public function test_executeTaskInCompany_disabledPersonnel_forbidden()
    {
        $this->personnel->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTaskInCompany(), 'Forbidden', 'only active personnel can  make this request');
    }
    public function test_executeTaskInCompany_taskForManagerOnly_hasNoActiveManager()
    {
        $this->task = $this->buildMockOfInterface(PersonnelHavingManagerAssignmentTaskInCompany::class);
        $this->personnel->managerAssignments->clear();
        $this->assertRegularExceptionThrowed(fn() => $this->executeTaskInCompany(), 'Forbidden', 'only active personnel having manager assignment can  make this request');
    }
    public function test_executeTaskInCompany_taskForManagerOnly_hasInativeManagerAssignment()
    {
        $this->task = $this->buildMockOfInterface(PersonnelHavingManagerAssignmentTaskInCompany::class);
        $this->manager->expects($this->any())
                ->method('isDisabled')
                ->willReturn(true);
        $this->assertRegularExceptionThrowed(fn() => $this->executeTaskInCompany(), 'Forbidden', 'only active personnel having manager assignment can  make this request');
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
    public Collection $managerAssignments;
}
