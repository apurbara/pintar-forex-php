<?php

namespace Company\Domain\Model\Manager\Sales\CustomerAssignment;

use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use DateTimeImmutable;
use Tests\TestBase;

class CustomerAssignmentJourneyTest extends TestBase
{
    protected $customerAssignment, $customerJourney;
    protected $id = 'newId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
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
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($customerAssignmentJourney->createdTime);
    }
}

class TestableCustomerAssignmentJourney extends CustomerAssignmentJourney
{
    public CustomerAssignment $customerAssignment;
    public CustomerJourney $customerJourney;
    public string $id;
    public DateTimeImmutable $createdTime;
}
