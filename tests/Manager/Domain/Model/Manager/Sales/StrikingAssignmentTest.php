<?php

namespace Manager\Domain\Model\Manager\Sales;

use Doctrine\Common\Collections\Collection;
use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Event\NegotiationClosed;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\SalesRole;
use Tests\TestBase;


class StrikingAssignmentTest extends TestBase
{
    protected $sales, $customer, $customerJourney;
    protected $strikingAssignment, $assignment;
    //
    protected $id = 'newId';
    //
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->strikingAssignment = new TestableStrikingAssignment($this->sales, $this->customer, 'id', $this->customerJourney);
        $this->assignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->strikingAssignment->customerAssignment = $this->assignment;
        //
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
    
    //
    protected function construct()
    {
        return new TestableStrikingAssignment($this->sales, $this->customer, $this->id, $this->customerJourney);
    }
    public function test_construct_setProperties()
    {
        $assignment = $this->construct();
        $this->assertSame($this->sales, $assignment->sales);
        $this->assertSame($this->customer, $assignment->customer);
        $this->assertSame($this->id, $assignment->id);
        $this->assertSame($this->customerJourney, $assignment->customerJourney);
        $this->assertEquals(CustomerAssignmentStatus::ACTIVE, $assignment->status);
        $this->assertInstanceOf(CustomerAssignment::class, $assignment->customerAssignment);
    }
    public function test_construct_assertSalesActive()
    {
        $this->sales->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_assertSalesHasStrikerRole()
    {
        $this->sales->expects($this->once())
                ->method('assertRoleEquals')
                ->with(SalesRole::STRIKER);
        $this->construct();
    }
    public function test_construct_assertCustomerHasNoActiveAssignment()
    {
        $this->customer->expects($this->once())
                ->method('assertHasNoActiveAssignment');
        $this->construct();
    }
    public function test_construct_assertCustomerStatusEqualsStrikingRequired()
    {
        $this->customer->expects($this->once())
                ->method('assertStatusEquals')
                ->with(CustomerStatus::STRIKING_REQUIRED);
        $this->construct();
    }
    public function test_construct_assertCustomerJourneyActive()
    {
        $this->customerJourney->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_emptyCustomerJourney()
    {
        $this->customerJourney = null;
        $this->construct();
        $this->markAsSuccess();
    }
    
    //
    protected function closeAssignment()
    {
        $this->strikingAssignment->closeAssignment();
    }
    public function test_closeAssignment_setAssignmentCompleted()
    {
        $this->closeAssignment();
        $this->assertEquals(CustomerAssignmentStatus::COMPLETED, $this->strikingAssignment->status);
    }
    public function test_closeAssignment_recordEvent()
    {
        $this->closeAssignment();
        $this->assertEquals(new NegotiationClosed($this->strikingAssignment->id), $this->strikingAssignment->recordedEvents[0]);
    }
    public function test_closeAssignment_completeAssignment()
    {
        $this->assignment->expects($this->once())
                ->method('completeAssignment');
        $this->closeAssignment();
    }
    public function test_closeAssignment_updateCustomerStatusToGoodFund()
    {
        $this->customer->expects($this->once())
                ->method('updateStatus')
                ->with(CustomerStatus::GOOD_FUND);
        $this->closeAssignment();
    }
    
    //
    protected function belongsToManager()
    {
        return $this->strikingAssignment->belongsToManager($this->manager);
    }
    public function test_belongsToManager_returnSalesComparisonResult()
    {
        $this->sales->expects($this->once())
                ->method('belongsToManager')
                ->with($this->manager)
                ->willReturn(true);
        $this->assertTrue($this->belongsToManager());
    }
    
}

class TestableStrikingAssignment extends StrikingAssignment
{
    public Sales $sales;
    public Customer $customer;
    public string $id = 'id';
    public CustomerAssignmentStatus $status;
    public CustomerAssignment $customerAssignment;
    public Collection $closingRequests;
    public ?CustomerJourney $customerJourney;
    //
    public $recordedEvents;
}
