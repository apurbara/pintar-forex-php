<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;

use DateTimeImmutable;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class RecycleRequestTest extends TestBase
{

    protected $recycleRequest;
    protected $assignedCustomer;
    //
    protected $remark = 'new remark';
    //
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recycleRequest = new TestableRecycleRequest();
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        $this->recycleRequest->assignedCustomer = $this->assignedCustomer;
        //
        $this->manager = $this->buildMockOfClass(Manager::class);
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
        $this->assignedCustomer->expects($this->once())
                ->method('recycleAssignment');
        $this->approve();
    }

    public function test_approve_appendCustomerAssignmentAsChildEvent()
    {
        $this->approve();
        $this->assertEquals($this->assignedCustomer, $this->recycleRequest->childrenContainEvents[0]);
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

    //
    protected function assertManageableByManager()
    {
        $this->recycleRequest->assertManageableByManager($this->manager);
    }

    public function test_assertManageableByManager_unmanagedAssignedCustomer_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertManageableByManager(), 'Forbidden',
                'unmanaged recycle request');
    }

    public function test_assertManageableByManager_managedAssignedCustomer_void()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('isManageableByManager')
                ->with($this->manager)
                ->willReturn(true);
        $this->assertManageableByManager();
        $this->markAsSuccess();
    }
}

class TestableRecycleRequest extends RecycleRequest
{

    public AssignedCustomer $assignedCustomer;
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
