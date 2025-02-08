<?php

namespace Manager\Domain\DependencyModel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Model\Manager\Sales\GreetingAssignment;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment;
use PHPUnit\Framework\MockObject\MockObject;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $customer;
    protected MockObject $greetingAssignment, $factFindingAssignment, $strikingAssignment;
    //
    protected $id = 'newId', $name = 'new name', $phone = '0823123131', $email = 'newAddress@email.org', $source = 'new source';
    protected $status = CustomerStatus::GOOD_FUND;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = new TestableCustomer();
        $this->customer->status = CustomerStatus::NEW;
        
        $this->greetingAssignment = $this->buildMockOfClass(GreetingAssignment::class);
        $this->customer->greetingAssignments = new ArrayCollection();
        $this->customer->greetingAssignments->add($this->greetingAssignment);
        
        $this->factFindingAssignment = $this->buildMockOfClass(FactFindingAssignment::class);
        $this->customer->factFindingAssignments = new ArrayCollection();
        $this->customer->factFindingAssignments->add($this->factFindingAssignment);
        
        $this->strikingAssignment = $this->buildMockOfClass(StrikingAssignment::class);
        $this->customer->strikingAssignments = new ArrayCollection();
        $this->customer->strikingAssignments->add($this->strikingAssignment);
        
    }
    
    //
    protected function updateStatus()
    {
        $this->customer->updateStatus($this->status);
    }
    public function test_updateStatus_updateStatus()
    {
        $this->updateStatus();
        $this->assertEquals($this->status, $this->customer->status);
    }
    
    //
    protected function assertHasNoActiveAssignment()
    {
        $this->greetingAssignment->expects($this->any()) ->method('getStatus') ->willReturn(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM);
        $this->factFindingAssignment->expects($this->any()) ->method('getStatus') ->willReturn(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM);
        $this->strikingAssignment->expects($this->any()) ->method('getStatus') ->willReturn(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM);
        $this->customer->assertHasNoActiveAssignment();
    }
    public function test_assertHasNoActiveAssignment_noActiveAssignment_void()
    {
        $this->assertHasNoActiveAssignment();
        $this->markAsSuccess();
    }
    public function test_assertHasNoActiveAssignment_hasActiveGreetingAssignment_forbidden()
    {
        $this->greetingAssignment->expects($this->any()) ->method('getStatus') ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->assertRegularExceptionThrowed(fn() => $this->assertHasNoActiveAssignment(), 'Forbidden', 'customer already being maintained');
    }
    public function test_assertHasNoActiveAssignment_hasActiveFactFindingAssignment_forbidden()
    {
        $this->factFindingAssignment->expects($this->any()) ->method('getStatus') ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->assertRegularExceptionThrowed(fn() => $this->assertHasNoActiveAssignment(), 'Forbidden', 'customer already being maintained');
    }
    public function test_assertHasNoActiveAssignment_hasActiveStrikingAssignment_forbidden()
    {
        $this->strikingAssignment->expects($this->any()) ->method('getStatus') ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->assertRegularExceptionThrowed(fn() => $this->assertHasNoActiveAssignment(), 'Forbidden', 'customer already being maintained');
    }
    
    protected function assertStatusEquals()
    {
        $this->customer->assertStatusEquals(CustomerStatus::NEW);
    }
    public function test_assertStatusEquals_differentStatus_forbidden()
    {
        $this->customer->status = CustomerStatus::FACT_FINDING_REQUIRED;
        $this->assertRegularExceptionThrowed(fn() => $this->assertStatusEquals(), 'Forbidden', 'unmatch customer status');
    }
    public function test_assertStatusEquals_samesStatus_void()
    {
        $this->assertStatusEquals();
        $this->markAsSuccess();
    }
}

class TestableCustomer extends Customer
{

    public string $id = 'id';
    public CustomerStatus $status;
    public Collection $greetingAssignments;
    public Collection $factFindingAssignments;
    public Collection $strikingAssignments;
    
    function __construct()
    {
        parent::__construct();
    }
}
