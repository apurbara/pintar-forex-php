<?php

namespace Manager\Domain\Model\Personnel\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Manager\Domain\Model\AreaStructure\Area;
use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\CustomerJourney;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;
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
    protected function receiveCustomerAssignment()
    {
        return $this->sales->receiveCustomerAssignment($this->assignedCustomerId, $this->customer, $this->customerJourney);
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
}

class TestableSales extends Sales
{

    public Manager $manager;
    public Area $area;
    public string $id;
    public bool $disabled = false;
    public SalesType $type;
    public Collection $assignedCustomers;
    
    function __construct()
    {
        parent::__construct();
    }
}
