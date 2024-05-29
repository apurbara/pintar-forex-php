<?php

namespace Sales\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sales\Domain\DependencyModel\AreaStructure\Area;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\Personnel;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\BySales\SalesTask;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use Tests\TestBase;

class SalesTest extends TestBase
{
    protected $sales, $personnel;
    protected $customerAssignment;
    //
    protected $task, $payload = 'string represent task payload';
    //
    protected $area;
    protected $customerJourney;
    //
    protected $schedulerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = new TestableSales();
        
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->personnel = $this->buildMockOfClass(Personnel::class);
        
        $this->sales->personnel = $this->personnel;
        $this->sales->customerAssignments = new ArrayCollection();
        $this->sales->customerAssignments->add($this->customerAssignment);
        //
        $this->task = $this->buildMockOfInterface(SalesTask::class);
        //
        $this->area = $this->buildMockOfClass(Area::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
    }
    
    //
    protected function assertActive()
    {
        $this->sales->assertActive();
    }
    public function test_assertActive_inactiveSales_forbidden()
    {
        $this->sales->disabled = true;
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
        $this->sales->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTask(), 'Forbidden', 'only active sales can make this request');
    }
    public function test_executeTask_assertPersonnelActive()
    {
        $this->personnel->expects($this->once())
                ->method('assertActive');
        $this->executeTask();
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
    public Personnel $personnel;
    public string $id = 'id';
    public bool $disabled = false;
    public Collection $customerAssignments;
    
    function __construct()
    {
        parent::__construct();
    }
}
