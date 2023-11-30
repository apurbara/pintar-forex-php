<?php

namespace Sales\Domain\Model;

use Sales\Domain\Service\SalesActivitySchedulerService;
use Tests\TestBase;

class SalesActivityTest extends TestBase
{
    protected $salesActivity;
    protected $schedulerService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->salesActivity = new TestableSalesActivity();
        $this->schedulerService = $this->buildMockOfClass(SalesActivitySchedulerService::class);
    }
    
    //
    public function test_isInitial_returnInitialStatus()
    {
        $this->assertFalse($this->salesActivity->isInitial());
        $this->salesActivity->initial = true;
        $this->assertTrue($this->salesActivity->isInitial());
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
    
    //
    protected function findAvailableTimeSlotForInitialActivity()
    {
        return $this->salesActivity->findAvailableTimeSlotForInitialActivity($this->schedulerService);
    }
    public function test_findAvailableTimeSlotForInitialActivity_returnSchedulernextAvailableTimeSlotForScheduleWithDurationResult()
    {
        $this->schedulerService->expects($this->once())
                ->method('nextAvailableTimeSlotForScheduleWithDuration')
                ->with($this->salesActivity->duration);
        $this->findAvailableTimeSlotForInitialActivity();
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
