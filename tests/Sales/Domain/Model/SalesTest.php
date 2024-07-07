<?php

namespace Sales\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\SalesTask;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\ValueObject\AccountInfo;
use Shared\Domain\ValueObject\ChangeUserPasswordData;
use Tests\TestBase;

class SalesTest extends TestBase
{
    protected $sales, $accountInfo;
    protected $customerAssignment;
    protected $newAccountInfo;
    protected $changePasswordData;
    protected $name = 'new name';
    protected $password = 'password123';
    //
    protected $task, $payload = 'string represent task payload';
    //
    protected $customerJourney;
    //
    protected $schedulerService;
    //
    //

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = new TestableSales();
        $this->accountInfo = $this->buildMockOfClass(AccountInfo::class);
        $this->newAccountInfo = $this->buildMockOfClass(AccountInfo::class);
        $this->sales->accountInfo = $this->accountInfo;
        
        $this->changePasswordData = $this->buildMockOfReadonlyClass(ChangeUserPasswordData::class);
        
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        
        $this->sales->customerAssignments = new ArrayCollection();
        $this->sales->customerAssignments->add($this->customerAssignment);
        //
        $this->task = $this->buildMockOfInterface(SalesTask::class);
        //
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
    }
    
    //
    protected function changePassword()
    {
        $this->sales->changePassword($this->changePasswordData);
    }
    public function test_changePassword_changeAccountInfoPassword()
    {
        $this->accountInfo->expects($this->once())
                ->method('changePassword')
                ->with($this->changePasswordData)
                ->willReturn($this->newAccountInfo);
        $this->changePassword();
        $this->assertSame($this->newAccountInfo, $this->sales->accountInfo);
    }
    
    //
    protected function changeName()
    {
        $this->sales->changeName($this->name);
    }
    public function test_changeName_changeAccountInfoPassword()
    {
        $this->accountInfo->expects($this->once())
                ->method('changeName')
                ->with($this->name)
                ->willReturn($this->newAccountInfo);
        $this->changeName();
        $this->assertSame($this->newAccountInfo, $this->sales->accountInfo);
    }
    
    //
    protected function login()
    {
        $this->accountInfo->expects($this->any())
                ->method('passwordMatch')
                ->willReturn(true);
        return $this->sales->login($this->password);
    }
    public function test_login_returnSalesId()
    {
        $this->assertSame($this->sales->id, $this->login());
    }
    public function test_login_disabledSales_forbidden()
    {
        $this->sales->contractTerminated = true;
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
    
    //
    protected function assertActive()
    {
        $this->sales->assertActive();
    }
    public function test_assertActive_inactiveSales_forbidden()
    {
        $this->sales->contractTerminated = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive sales');
    }
    public function test_assertActive_activeSales_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
    
    //
    protected function executeTask()
    {
        $this->sales->executeTask($this->task, $this->payload);
    }
    public function test_executeTask_executeTask()
    {
        $this->task->expects($this->once())
                ->method('executeBySales')
                ->with($this->sales, $this->payload);
        $this->executeTask();
    }
    public function test_executeTask_disabledSales_forbidden()
    {
        $this->sales->contractTerminated = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTask(), 'Forbidden', 'only active sales can make this request');
    }
    
    //
    protected function registerAllUpcomingScheduleToScheduler()
    {
        $this->customerAssignment->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->sales->registerAllUpcomingScheduleToScheduler($this->schedulerService);
    }
    public function test_registerAllUpcomingScheduleToScheduler_addCustomerAssignmentUpcomingScheduleToScheduler()
    {
        $this->customerAssignment->expects($this->once())
                ->method('addUpcomingScheduleToSchedulerService')
                ->with($this->schedulerService);
        $this->registerAllUpcomingScheduleToScheduler();
    }
    public function test_registerAllUpcomingScheduleToScheduler_containInactiveCustomerAssignment_excludeFromScheduler()
    {
        $this->customerAssignment->expects($this->once())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::RECYCLED);
        $this->customerAssignment->expects($this->never())
                ->method('addUpcomingScheduleToSchedulerService');
        $this->registerAllUpcomingScheduleToScheduler();
    }
}

class TestableSales extends Sales
{
    public string $id = 'id';
    public bool $contractTerminated = false;
    public Collection $customerAssignments;
    public AccountInfo $accountInfo;
    
    function __construct()
    {
        parent::__construct();
    }
}
