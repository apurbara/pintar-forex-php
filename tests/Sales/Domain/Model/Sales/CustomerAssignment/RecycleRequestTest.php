<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment;

use DateTimeImmutable;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class RecycleRequestTest extends TestBase
{
    protected $customerAssignment;
    protected $recycleRequest;
    //
    protected $id = 'newId', $note = 'new note';
    //
    protected $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $data = (new RecycleRequestData('note'))->setId('id');
        $this->recycleRequest = new TestableRecycleRequest($this->customerAssignment, $data);
        //
        $this->sales = $this->buildMockOfClass(Sales::class);
    }
    
    //
    protected function createData()
    {
        return (new RecycleRequestData($this->note))->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableRecycleRequest($this->customerAssignment, $this->createData());
    }
    public function test_constuct_setProperties()
    {
        $recycleRequest = $this->construct();
        $this->assertSame($this->customerAssignment, $recycleRequest->customerAssignment);
        $this->assertSame($this->id, $recycleRequest->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($recycleRequest->createdTime);
        $this->assertEquals(ManagementApprovalStatus::WAITING_FOR_APPROVAL, $recycleRequest->status);
        $this->assertSame($this->note, $recycleRequest->note);
    }
    
    //
    protected function update()
    {
        $this->recycleRequest->update($this->createData());
    }
    public function test_update_setProperties()
    {
        $this->update();
        $this->assertSame($this->note, $this->recycleRequest->note);
    }
    public function test_update_notOngoingRequest_forbidden()
    {
        $this->recycleRequest->status = ManagementApprovalStatus::REJECTED;
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Forbidden', 'request already concluded');
    }
    
    //
    protected function assertManageableBySales()
    {
        $this->recycleRequest->assertManageableBySales($this->sales);
    }
    public function test_assertManageableBySales_assertCustomerAssignmentBelongsToSales()
    {
        $this->customerAssignment->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->assertManageableBySales();
    }
    
    //
    protected function isOngoing()
    {
        return $this->recycleRequest->isOngoing();
    }
    public function test_isOngoing_ongoingRequest_returnTrue()
    {
        $this->assertTrue($this->isOngoing());
    }
    public function test_isOngoing_concludedRequest_returnFalse()
    {
        $this->recycleRequest->status = ManagementApprovalStatus::REJECTED;
        $this->assertFalse($this->isOngoing());
    }
}

class TestableRecycleRequest extends RecycleRequest
{
    public CustomerAssignment $customerAssignment;
    public string $id;
    public DateTimeImmutable $createdTime;
    public ManagementApprovalStatus $status;
    public ?string $note;
}
