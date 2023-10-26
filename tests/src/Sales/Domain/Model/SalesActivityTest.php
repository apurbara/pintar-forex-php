<?php

namespace Sales\Domain\Model;

use Tests\TestBase;

class SalesActivityTest extends TestBase
{
    protected $salesActivity;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->salesActivity = new TestableSalesActivity();
    }
    
    //
    protected function assertActive()
    {
        $this->salesActivity->assertActive();
    }
    public function test_assertActive_disabledActivity_forbidden()
    {
        $this->salesActivity->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive sales activity');
    }
    public function test_assertActive_activeActivity_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
}

class TestableSalesActivity extends SalesActivity
{
    public string $id = 'id';
    public bool $disabled = false;
    public int $duration = 15;
    public bool $initial = false;
    
    function __construct()
    {
        parent::__construct();
    }
}
