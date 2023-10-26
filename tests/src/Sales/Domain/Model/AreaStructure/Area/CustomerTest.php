<?php

namespace Sales\Domain\Model\AreaStructure\Area;

use DateTimeImmutable;
use Sales\Domain\Model\AreaStructure\Area;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $area;
    //
    protected $id = 'newId', $name = 'new customer name', $email = 'customer@email.org';
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->area = $this->buildMockOfClass(Area::class);
    }
    
    //
    protected function createData()
    {
        return (new CustomerData($this->name, $this->email))->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableCustomer($this->area, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $customer = $this->construct();
        $this->assertSame($this->id, $customer->id);
        $this->assertfalse($customer->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($customer->createdTime);
        $this->assertSame($this->name, $customer->name);
        $this->assertSame($this->email, $customer->email);
        $this->assertSame($this->area, $customer->area);
    }
    public function test_construct_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'customer name is mandatory');
    }
    public function test_construct_invalidMailFormat_badRequest()
    {
        $this->email = 'bad mail format';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'customer email is mandatory and must be in valid email address format');
    }
    
}

class TestableCustomer extends Customer
{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public string $name;
    public string $email;
    public Area $area;
}
