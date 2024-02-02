<?php

namespace Manager\Domain\Model\Personnel\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Manager\Domain\Model\AreaStructure\Area;
use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\CustomerJourney;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Resources\Exception\RegularException;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;
use Tests\TestBase;

class SalesTest extends TestBase
{

    protected $sales;
    protected $manager, $area;
    protected $assignedCustomerOne;
    protected $assignedCustomerTwo;
    //
    protected $customer;
    protected $assignedCustomerId = 'assignedCustomerId', $customerJourney;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = new TestableSales();

        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->sales->manager = $this->manager;

        $this->area = $this->buildMockOfClass(Area::class);
        $this->sales->area = $this->area;

        $this->sales->assignedCustomers = new ArrayCollection();
        $this->assignedCustomerOne = $this->buildMockOfClass(AssignedCustomer::class);
        $this->assignedCustomerTwo = $this->buildMockOfClass(AssignedCustomer::class);
        $this->sales->assignedCustomers->add($this->assignedCustomerOne);
        $this->sales->assignedCustomers->add($this->assignedCustomerTwo);
        //
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
    }

    //
    protected function isManageableByManager()
    {
        return $this->sales->isManageableByManager($this->manager);
    }

    public function test_isManageableByManager_sameManager_returnTrue()
    {
        $this->assertTrue($this->isManageableByManager());
    }

    public function test_isManageableByManager_diffManager_returnFalse()
    {
        $this->sales->manager = $this->buildMockOfClass(Manager::class);
        $this->assertFalse($this->isManageableByManager());
    }

    //
    protected function assertManageableByManager()
    {
        $this->sales->assertManageableByManager($this->manager);
    }

    public function test_assertManageableByManager_diffManager_forbidden()
    {
        $this->sales->manager = $this->buildMockOfClass(Manager::class);
        $this->assertRegularExceptionThrowed(fn() => $this->assertManageableByManager(), 'Forbidden', 'unmanaged sales');
    }

    public function test_assertManageableByManager_sameManager_void()
    {
        $this->assertManageableByManager();
        $this->markAsSuccess();
    }

    //
    protected function receiveCustomerAssignment()
    {
        return $this->sales->receiveCustomerAssignment($this->assignedCustomerId, $this->customer,
                        $this->customerJourney);
    }

    public function test_receiveCustomerAssignment_returnCustomerAssignment()
    {
        $this->assertInstanceOf(AssignedCustomer::class, $this->receiveCustomerAssignment());
    }

    public function test_receiveCustomerAssignment_inactiveSales_forbidden()
    {
        $this->sales->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->receiveCustomerAssignment(), 'Forbidden', 'inactive sales');
    }
    public function test_receiveCustomerAssignment_storeEvent()
    {
        $this->receiveCustomerAssignment();
        $event = (new MultipleCustomerAssignmentReceivedBySales($this->sales->id))
                ->addAssignedCustomerIdList($this->assignedCustomerId);
        $this->assertEquals($event, $this->sales->recordedEvents[0]);
    }
    public function test_receiveCustomerAssignment_consecutiveAssignmentReceived_storeEvent()
    {
        $otherCustomer = $this->buildMockOfClass(Customer::class);
        $this->sales->receiveCustomerAssignment($assignedCustomerId = 'assignedCusstomerId', $this->customer, $this->customerJourney);
        $this->sales->receiveCustomerAssignment($otherAssignedCustomerId = 'otherCustomerAssignmentId', $otherCustomer, $this->customerJourney);
        $event = (new MultipleCustomerAssignmentReceivedBySales($this->sales->id))
                ->addAssignedCustomerIdList($assignedCustomerId)
                ->addAssignedCustomerIdList($otherAssignedCustomerId);
        $this->assertEquals($event, $this->sales->recordedEvents[0]);
    }
    public function test_receiveCustomerAssignment_consecutiveAssignmentReceived_storeOnlySingleEvent()
    {
        $otherCustomer = $this->buildMockOfClass(Customer::class);
        $this->sales->receiveCustomerAssignment($assignedCustomerId = 'assignedCusstomerId', $this->customer, $this->customerJourney);
        $this->sales->receiveCustomerAssignment($otherAssignedCustomerId = 'otherCustomerAssignmentId', $otherCustomer, $this->customerJourney);
        $event = (new MultipleCustomerAssignmentReceivedBySales($this->sales->id))
                ->addAssignedCustomerIdList($assignedCustomerId)
                ->addAssignedCustomerIdList($otherAssignedCustomerId);
        $this->assertEquals($event, $this->sales->recordedEvents[0]);
        $this->assertEquals(1, count($this->sales->recordedEvents));
    }
    public function test_receiveCustomerAssignment_consecutiveAssignmentReceived_assignmentCausedError_ignoreFailAssignment()
    {
        $this->customer->expects($this->any())
                ->method('assertHasNoActiveAssignment')
                ->willThrowException(RegularException::forbidden('unassignable customer'));
        $this->receiveCustomerAssignment();
        $otherCustomer = $this->buildMockOfClass(Customer::class);
        $this->sales->receiveCustomerAssignment($otherAssignedCustomerId = 'otherCustomerAssignmentId', $otherCustomer, $this->customerJourney);
        $event = (new MultipleCustomerAssignmentReceivedBySales($this->sales->id))
                ->addAssignedCustomerIdList($otherAssignedCustomerId);
        $this->assertEquals($event, $this->sales->recordedEvents[0]);
    }
    public function test_receiveCustomerAssignment_incrementActiveAssignmentValue()
    {
        $this->receiveCustomerAssignment();
        $this->assertSame(1.0, $this->sales->activeAssignmentValue);
        //
        $this->sales->activeAssignmentValue = 3.0;
        $this->receiveCustomerAssignment();
        $this->assertSame(4.0, $this->sales->activeAssignmentValue);
    }

    //
    protected function countAssignmentPriorityWithCustomer()
    {
        $this->customer->expects($this->any())->method('areaEquals')->willReturn(true);
        $this->assignedCustomerOne->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->assignedCustomerTwo->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        return $this->sales->countAssignmentPriorityWithCustomer($this->customer);
    }

    public function test_countAssignmentPriorityWithCustomer_returnAssignedCustomerCount()
    {
        $this->assertEquals(2, $this->countAssignmentPriorityWithCustomer());
    }

    public function test_countAssignmentPriorityWithCustomer_containInactiveAssignment_excludeFromCount()
    {
        $this->assignedCustomerOne->expects($this->once())->method('getStatus')->willReturn(CustomerAssignmentStatus::RECYCLED);
        $this->assertEquals(1, $this->countAssignmentPriorityWithCustomer());
    }

    public function test_countAssignmentPriorityWithCustomer_areaNotEqualsWithCustomerArea()
    {
        $this->customer->expects($this->once())
                ->method('areaEquals')
                ->with($this->area)
                ->willReturn(false);
        $this->assertSame(INF, $this->countAssignmentPriorityWithCustomer());
    }
    
    //
    protected function countActiveAssignmentValue()
    {
        $this->assignedCustomerOne->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->assignedCustomerTwo->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        return $this->sales->countActiveAssignmentValue();
    }
    public function test_countActiveAssignmentValue_returnActiveAssignmentValue()
    {
        $this->assertEquals(2.0, $this->countActiveAssignmentValue());
    }
    public function test_countActiveAssignmentValue_containInactiveAssignment_excludeFromCount()
    {
        $this->assignedCustomerOne->expects($this->once())->method('getStatus')->willReturn(CustomerAssignmentStatus::RECYCLED);
        $this->assertEquals(1.0, $this->countActiveAssignmentValue());
    }
    public function test_countActiveAssignmentValue_activeAssignmentValueAlreadyDefined_preventRecount()
    {
        $this->sales->activeAssignmentValue = 5.0;
        $this->assignedCustomerOne->expects($this->never())->method('getStatus');
        $this->assignedCustomerTwo->expects($this->never())->method('getStatus');
        $this->assertEquals(5.0, $this->countActiveAssignmentValue());
    }
}

class TestableSales extends Sales
{

    public Manager $manager;
    public Area $area;
    public string $id = 'salesId';
    public bool $disabled = false;
    public SalesType $type;
    public Collection $assignedCustomers;
    
    public $recordedEvents;
    public ?MultipleCustomerAssignmentReceivedBySales $multipleCustomerAssignmentReceivedBySalesEvent;
    public ?float $activeAssignmentValue = null;

    function __construct()
    {
        parent::__construct();
    }
}
