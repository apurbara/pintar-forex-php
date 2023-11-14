<?php

namespace Sales\Domain\Model;

use Tests\TestBase;

class CustomerJourneyTest extends TestBase
{
    protected $customerJourney;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerJourney = new TestableCustomerJourney();
    }
    
    //
    protected function assertActive()
    {
        $this->customerJourney->assertActive();
    }
    public function test_assertActive_disabled_forbidden()
    {
        $this->customerJourney->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive customer journey');
    }
    public function test_assertActive_active_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
}

class TestableCustomerJourney extends CustomerJourney
{
    public string $id = 'id';
    public bool $disabled = false;
    public bool $initial = false;
    
    function __construct()
    {
        parent::__construct();
    }
}
