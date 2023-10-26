<?php

namespace Sales\Domain\Model\AreaStructure;

use Tests\TestBase;

class AreaTest extends TestBase
{
    protected $area;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->area = new TestableArea();
    }
    
    //
    protected function assertAccessible()
    {
        $this->area->assertAccessible();
    }
    public function test_assertAccessible_disabledArea_forbidden()
    {
        $this->area->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertAccessible(), 'Forbidden', 'inaccessible area');
    }
    public function test_assertAccessible_activeArea_void()
    {
        $this->assertAccessible();
        $this->markAsSuccess();
    }
    
    //
    protected function createCustomer()
    {
        $customerData = (new Area\CustomerData('customer name', 'customer@email.org'))->setId('customerId');
        return $this->area->createCustomer($customerData);
    }
    public function test_createCustomer_returnCustomer()
    {
        $this->assertInstanceOf(Area\Customer::class, $this->createCustomer());
    }
}

class TestableArea extends Area
{
    public string $id = 'areaId';
    public bool $disabled = false;
    
    function __construct()
    {
        parent::__construct();
    }
}
