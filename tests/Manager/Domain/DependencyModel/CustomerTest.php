<?php

namespace Manager\Domain\DependencyModel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $customer;
    protected $customerAssignment;
    //
    protected $id = 'newId', $name = 'new name', $phone = '0823123131', $email = 'newAddress@email.org', $source = 'new source';

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = new TestableCustomer();
        
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->customer->customerAssignments = new ArrayCollection();
        $this->customer->customerAssignments->add($this->customerAssignment);
    }
    
    //
    protected function assertActive()
    {
        $this->customer->assertActive();
    }
    public function test_assertActive_disabledCustomer_forbidden()
    {
        $this->customer->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive customer');
    }
    public function test_assertActive_activeCustomer_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }

    //
    protected function assertHasNoActiveAssignment()
    {
        $this->customerAssignment->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->customer->assertHasNoActiveAssignment();
    }
    public function test_assertHasNoActiveAssignment_hasActiveAssignment_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertHasNoActiveAssignment(), 'Forbidden', 'customer already being maintained');
    }
    public function test_assertHasNoActiveAssignment_noActiveAssignment_void()
    {
        $this->customerAssignment->expects($this->once())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::RECYCLED);
        $this->assertHasNoActiveAssignment();
        $this->markAsSuccess();
    }
}

class TestableCustomer extends Customer
{

    public string $id = 'id';
    public bool $disabled = false;
    public Collection $customerAssignments;
    
    function __construct()
    {
        parent::__construct();
    }
}
