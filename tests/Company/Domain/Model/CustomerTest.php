<?php

namespace Company\Domain\Model;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerData;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Model\Province\City;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $city;
    protected $customer;
    protected MockObject $greetingAssignment, $factFindingAssignment, $strikingAssignment;
    //
    protected $id = 'newId', $name = 'new name', $phone = '0823123131', $email = 'newAddress@email.org', $source = 'new source';

    protected function setUp(): void
    {
        parent::setUp();
        $this->city = $this->buildMockOfClass(City::class);
        //
        $data = (new CustomerData())
                ->setName('name')
                ->setEmail('customer@email.org')
                ->setPhone('082131231');
        $this->customer = new TestableCustomer($this->city, 'id', $data);
        
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
    protected function createData()
    {
        return (new CustomerData())
                        ->setName($this->name)
                        ->setPhone($this->phone)
                        ->setEmail($this->email)
                        ->setSource($this->source);
    }
    
    //
    protected function construct()
    {
        return new TestableCustomer($this->city, $this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $customer = $this->construct();
        $this->assertSame($this->city, $customer->city);
        $this->assertSame($this->id, $customer->id);
        $this->assertFalse($customer->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($customer->createdTime);
        $this->assertSame(CustomerStatus::NEW, $customer->status);
        $this->assertSame($this->name, $customer->name);
        $this->assertSame($this->phone, $customer->phone);
        $this->assertSame($this->email, $customer->email);
        $this->assertSame($this->source, $customer->source);
    }
    public function test_construct_nullArea()
    {
        $this->city = null;
        $this->construct();
        $this->markAsSuccess();
    }
    public function test_construct_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'customer name is mandatory');
    }
    public function test_construct_invalidPhone_badRequest()
    {
        $this->phone = 'bad phone format';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'valid customer phone is mandatory');
    }
    public function test_construct_invalidEmail_badRequest()
    {
        $this->email = 'bad email format';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'invalid customer mail address format');
    }
    public function test_construct_emptyEmail_200()
    {
        $this->email = null;
        $this->construct();
        $this->markAsSuccess();
    }
    public function test_construct_assertAreaActive()
    {
        $this->city->expects($this->once())
                ->method('assertActive');
        $this->construct();
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

    public ?City $city;
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public CustomerStatus $status;
    public string $name;
    public ?string $email;
    public string $phone;
    public ?string $source;
    public Collection $greetingAssignments;
    public Collection $factFindingAssignments;
    public Collection $strikingAssignments;
}
