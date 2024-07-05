<?php

namespace Sales\Domain\DependencyModel\Province;

use Tests\TestBase;

class CityTest extends TestBase
{
    protected $city;
    
    protected function setUp(): void
    {
        $this->city = new TestableCity();
    }
    
    //
    protected function assertActive()
    {
        $this->city->assertActive();
    }
    public function test_assertActive_inactiveCity_forbidden()
    {
        $this->city->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive city');
    }
    public function test_assertActive_activeCity_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
}

class TestableCity extends City
{
    public string $id;
    public bool $disabled = false;
    
    function __construct()
    {
    }
}
