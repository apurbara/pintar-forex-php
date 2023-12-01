<?php

namespace Manager\Domain\Model\AreaStructure\Area;

use Manager\Domain\Model\AreaStructure\Area;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $area;
    protected $customer;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->area = $this->buildMockOfClass(Area::class);
        
        $this->customer = new TestableCustomer();
        $this->customer->area = $this->area;
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
}

class TestableCustomer extends Customer
{
    public Area $area;
    public string $id;
    
    function __construct()
    {
        parent::__construct();
    }
}
