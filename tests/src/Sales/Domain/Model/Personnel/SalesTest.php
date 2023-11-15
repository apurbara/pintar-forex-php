<?php

namespace Sales\Domain\Model\Personnel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\CustomerJourney;
use Sales\Domain\Model\Personnel;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\SalesTask;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use Tests\TestBase;

class SalesTest extends TestBase
{
    protected $sales;
    protected $assignedCustomer;
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
        
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        $this->sales->assignedCustomers = new ArrayCollection();
        $this->sales->assignedCustomers->add($this->assignedCustomer);
        //
        $this->task = $this->buildMockOfInterface(SalesTask::class);
        //
        $this->area = $this->buildMockOfClass(Area::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
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
    
    //
    protected function registerNewCustomer()
    {
        $customerData = (new Area\CustomerData('customer name', 'customer@email.org', '08131231232'))->setId('customerId');
        return $this->sales->registerNewCustomer($this->area, $this->customerJourney, 'assigmentId', $customerData);
    }
    public function test_registerNewCustomer_returnNewManagedCustomer()
    {
        $this->area->expects($this->once())
                ->method('createCustomer');
        $this->assertInstanceOf(AssignedCustomer::class, $this->registerNewCustomer());
    }
    public function test_registerNewCustomer_emptyInitialJourney_void()
    {
        $this->customerJourney = null;
        $this->registerNewCustomer();
        $this->markAsSuccess();
    }
    
    //
    protected function registerAllUpcomingScheduleToScheduler()
    {
        $this->assignedCustomer->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->sales->registerAllUpcomingScheduleToScheduler($this->schedulerService);
    }
    public function test_registerAllUpcomingScheduleToScheduler_addAssignedCustomerUpcomingScheduleToScheduler()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('addUpcomingScheduleToSchedulerService')
                ->with($this->schedulerService);
        $this->registerAllUpcomingScheduleToScheduler();
    }
    public function test_registerAllUpcomingScheduleToScheduler_containInactiveCustomerAssignment_excludeFromScheduler()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::RECYCLED);
        $this->assignedCustomer->expects($this->never())
                ->method('addUpcomingScheduleToSchedulerService');
        $this->registerAllUpcomingScheduleToScheduler();
    }
}

class TestableSales extends Sales
{
    public Personnel $personnel;
    public string $id = 'id';
    public bool $disabled = false;
    public Collection $assignedCustomers;
    
    function __construct()
    {
        parent::__construct();
    }
}
