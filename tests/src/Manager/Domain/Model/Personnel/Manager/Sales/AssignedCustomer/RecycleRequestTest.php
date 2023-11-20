<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;

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
    protected function approve()
    {
        $this->recycleRequest->approve();
    }
    public function test_approve_setStatusApproved()
    {
        $this->approve();
        $this->assertEquals(ManagementApprovalStatus::APPROVED, $this->recycleRequest->status);
    }
    public function test_approve_noWaitingStatu_forbidden()
    {
        $this->recycleRequest->status = ManagementApprovalStatus::REJECTED;
        $this->assertRegularExceptionThrowed(fn() => $this->approve(), 'Forbidden', 'unable to process concluded request');
    }
    public function test_approve_closeCustomerAssignment()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('recycleAssignment');
        $this->approve();
    }
    
    //
    protected function reject()
    {
        $this->recycleRequest->reject();
    }
    public function test_reject_setStatusApproved()
    {
        $this->reject();
        $this->assertEquals(ManagementApprovalStatus::REJECTED, $this->recycleRequest->status);
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
        $this->assertRegularExceptionThrowed(fn() => $this->assertManageableByManager(), 'Forbidden', 'unmanaged recycle request');
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
    public ManagementApprovalStatus $status;
    
    function __construct()
    {
        parent::__construct();
        $this->status = ManagementApprovalStatus::WAITING_FOR_APPROVAL;
    }
}
