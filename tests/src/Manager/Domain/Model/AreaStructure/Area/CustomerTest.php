<?php

namespace Manager\Domain\Model\AreaStructure\Area;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Manager\Domain\Model\AreaStructure\Area;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $area;
    protected $customer;
    protected $assignedCustomer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->area = $this->buildMockOfClass(Area::class);
        
        $this->customer = new TestableCustomer();
        $this->customer->area = $this->area;
        
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        $this->customer->assignedCustomers = new ArrayCollection();
        $this->customer->assignedCustomers->add($this->assignedCustomer);
    }
    
    //
    protected function areaEquals()
    {
        return $this->customer->areaEquals($this->area);
    }
    public function test_areaEquals_sameArea_returnTrue()
    {
        $this->assertTrue($this->areaEquals());
    }
    public function test_areaEquals_differentArea_returnFalse()
    {
        $this->customer->area = $this->buildMockOfClass(Area::class);
        $this->assertFalse($this->areaEquals());
    }
    
    //
    protected function assertHasNoActiveAssignment()
    {
        $this->assignedCustomer->expects($this->any())
                ->method('getStatus')
                ->willReturn(\SharedContext\Domain\Enum\CustomerAssignmentStatus::ACTIVE);
        $this->customer->assertHasNoActiveAssignment();
    }
    public function test_assertHasNoActiveAssignment_hasActiveAssignment_throwRegularException()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertHasNoActiveAssignment(), 'Forbidden', 'customer has active assignment');
    }
    public function test_assertHasNoActiveAssignment_noActiveAssignment_void()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('getStatus')
                ->willReturn(\SharedContext\Domain\Enum\CustomerAssignmentStatus::RECYCLED);
        $this->assertHasNoActiveAssignment();
        $this->markAsSuccess();
    }
}

class TestableCustomer extends Customer
{
    public Area $area;
    public string $id;
    public Collection $assignedCustomers;
    
    function __construct()
    {
        parent::__construct();
    }
}
