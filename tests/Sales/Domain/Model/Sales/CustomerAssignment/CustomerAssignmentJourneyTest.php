<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment;

use DateTimeImmutable;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Tests\TestBase;

class CustomerAssignmentJourneyTest extends TestBase
{
    protected $customerAssignment, $customerJourney;
    protected $customerAssignmentJourney;
    protected $id = 'newId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->customerAssignmentJourney = new TestableCustomerAssignmentJourney($this->customerAssignment, $this->customerJourney, 'id');
    }
    
    //
    protected function construct()
    {
        return new TestableCustomerAssignmentJourney($this->customerAssignment, $this->customerJourney, $this->id);
    }
    public function test_construct_setProperties()
    {
        $customerAssignmentJourney = $this->construct();
        $this->assertSame($this->customerAssignment, $customerAssignmentJourney->customerAssignment);
        $this->assertSame($this->customerJourney, $customerAssignmentJourney->customerJourney);
        $this->assertSame($this->id, $customerAssignmentJourney->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($customerAssignmentJourney->startTime);
    }
    
    //
    protected function completeJourney()
    {
        $this->customerAssignmentJourney->completeJourney();
    }
    public function test_completeJourney_setEndTime()
    {
        $this->completeJourney();
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($this->customerAssignmentJourney->endTime);
    }
}

class TestableCustomerAssignmentJourney extends CustomerAssignmentJourney
{
    public CustomerAssignment $customerAssignment;
    public CustomerJourney $customerJourney;
    public string $id;
    public DateTimeImmutable $startTime;
    public ?DateTimeImmutable $endTime;
}
