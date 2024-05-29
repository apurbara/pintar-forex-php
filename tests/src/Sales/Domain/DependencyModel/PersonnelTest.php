<?php

namespace Sales\Domain\DependencyModel;

use Tests\TestBase;

class PersonnelTest extends TestBase
{
    protected $personnel;
    
    protected function setUp(): void
    {
        $this->personnel = new TestablePersonnel();
    }
    
    //
    protected function assertActive()
    {
        $this->personnel->assertActive();
    }
    public function test_assertActive_suspendedPersonnel_forbidden()
    {
        $this->personnel->suspended = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive personnel');
    }
    public function test_assertActive_activePersonnel_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
}

class TestablePersonnel extends Personnel
{
    public string $id;
    public bool $suspended = false;
    
    function __construct()
    {
        parent::__construct();
    }
}
