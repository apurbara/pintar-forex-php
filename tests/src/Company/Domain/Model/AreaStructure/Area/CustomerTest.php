<?php

namespace Company\Domain\Model\AreaStructure\Area;

use Company\Domain\Model\AreaStructure\Area;
use DateTimeImmutable;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $area;
    protected $id = 'newId', $name = 'new name', $phone = '0823123131', $email = 'newAddress@email.org', $source = 'new source';

    protected function setUp(): void
    {
        parent::setUp();
        $this->area = $this->buildMockOfClass(Area::class);
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
        return new TestableCustomer($this->area, $this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $customer = $this->construct();
        $this->assertSame($this->area, $customer->area);
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
        $this->area = null;
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
        $this->email = '';
        $this->construct();
        $this->markAsSuccess();
    }
}

class TestableCustomer extends Customer
{

    public ?Area $area;
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public string $name;
    public ?string $email;
    public string $phone;
    public ?string $source;
}
