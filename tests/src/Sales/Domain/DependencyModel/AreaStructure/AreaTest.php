<?php

namespace Sales\Domain\DependencyModel\AreaStructure;

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
    protected function assertActive()
    {
        $this->area->assertActive();
    }
    public function test_assertActive_disabledArea_forbidden()
    {
        $this->area->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive area');
    }
    public function test_assertActive_activeArea_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
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
