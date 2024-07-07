<?php

namespace Company\Domain\Model;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerData;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Province\City;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $city;
    protected $customer;
    protected $customerAssignment;
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
        
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->customer->customerAssignments = new ArrayCollection();
        $this->customer->customerAssignments->add($this->customerAssignment);
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
    
    //
    protected function hasActiveAssignment()
    {
        $this->customerAssignment->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        return $this->customer->hasActiveAssignment();
    }
    public function test_hasActiveAssignment_hasActiveAssignment_returnTrue()
    {
        $this->assertTrue($this->hasActiveAssignment());
    }
    public function test_hasActiveAssignment_hasNoAssignment_returnFalse()
    {
        $this->customerAssignment->expects($this->once())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::RECYCLED);
        $this->assertFalse($this->hasActiveAssignment());
    }
}

class TestableCustomer extends Customer
{

    public ?City $city;
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public string $name;
    public ?string $email;
    public string $phone;
    public ?string $source;
    public Collection $customerAssignments;
}
