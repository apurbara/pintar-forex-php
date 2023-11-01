<?php

namespace Sales\Domain\Model;

use Tests\TestBase;

class CustomerVerificationTest extends TestBase
{
    protected $customerVerification;
    
    protected function setUp(): void
    {
        
        parent::setUp();
        $this->customerVerification = new TestableCustomerVerification();
    }
    
    //
    protected function assertActive()
    {
        $this->customerVerification->assertActive();
    }
    public function test_assertActive_disabledVerification_forbidden()
    {
        $this->customerVerification->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive customer verification');
    }
    public function test_assertActive_activeVerification_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
}

class TestableCustomerVerification extends CustomerVerification
{
    public string $id = 'id';
    public bool $disabled = false;
    
    function __construct()
    {
        parent::__construct();
    }
}
