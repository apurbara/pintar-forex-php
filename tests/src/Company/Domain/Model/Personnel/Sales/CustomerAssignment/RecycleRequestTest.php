<?php

namespace Company\Domain\Model\Personnel\Sales\CustomerAssignment;

use Company\Domain\Model\Personnel\Sales\CustomerAssignment;
use DateTimeImmutable;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class RecycleRequestTest extends TestBase
{

    protected $recycleRequest;
    protected $customerAssignment;
    //
    protected $remark = 'new remark';

    protected function setUp(): void
    {
        parent::setUp();
        $this->recycleRequest = new TestableRecycleRequest();
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->recycleRequest->customerAssignment = $this->customerAssignment;
    }

    //
    protected function getRecycleRequestData()
    {
        return (new RecycleRequestData())
                        ->setRemark($this->remark);
    }

    //
    protected function approve()
    {
        $this->recycleRequest->approve($this->getRecycleRequestData());
    }
    public function test_approve_setProperties()
    {
        $this->approve();
        $this->assertEquals(ManagementApprovalStatus::APPROVED, $this->recycleRequest->status);
        $this->assertSame($this->remark, $this->recycleRequest->remark);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($this->recycleRequest->concludedTime);
    }
    public function test_approve_noWaitingStatus_forbidden()
    {
        $this->recycleRequest->status = ManagementApprovalStatus::REJECTED;
        $this->assertRegularExceptionThrowed(fn() => $this->approve(), 'Forbidden',
                'unable to process concluded request');
    }
    public function test_approve_closeCustomerAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('recycle');
        $this->approve();
    }
    public function test_approve_appendCustomerAssignmentAsChildEvent()
    {
        $this->approve();
        $this->assertEquals($this->customerAssignment, $this->recycleRequest->childrenContainEvents[0]);
    }

    //
    protected function reject()
    {
        $this->recycleRequest->reject($this->getRecycleRequestData());
    }
    public function test_reject_setProperties()
    {
        $this->reject();
        $this->assertEquals(ManagementApprovalStatus::REJECTED, $this->recycleRequest->status);
        $this->assertSame($this->remark, $this->recycleRequest->remark);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($this->recycleRequest->concludedTime);
    }
    public function test_reject_noWaitingStatu_forbidden()
    {
        $this->recycleRequest->status = ManagementApprovalStatus::APPROVED;
        $this->assertRegularExceptionThrowed(fn() => $this->reject(), 'Forbidden', 'unable to process concluded request');
    }

}

class TestableRecycleRequest extends RecycleRequest
{

    public CustomerAssignment $customerAssignment;
    public string $id;
    public DateTimeImmutable $createdTime;
    public DateTimeImmutable $concludedTime;
    public ManagementApprovalStatus $status;
    public ?string $note;
    public ?string $remark;
    public $childrenContainEvents = [];

    function __construct()
    {
        parent::__construct();
        $this->status = ManagementApprovalStatus::WAITING_FOR_APPROVAL;
    }
}
