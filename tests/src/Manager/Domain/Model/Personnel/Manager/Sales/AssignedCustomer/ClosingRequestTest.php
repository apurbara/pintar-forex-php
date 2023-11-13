<?php

namespace Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class ClosingRequestTest extends TestBase
{
    protected $closingRequest;
    protected $assignedCustomer;
    //
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->closingRequest = new TestableClosingRequest();
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        $this->closingRequest->assignedCustomer = $this->assignedCustomer;
        //
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
    
    //
    protected function accept()
    {
        $this->closingRequest->accept();
    }
    public function test_accept_setStatusApproved()
    {
        $this->accept();
        $this->assertEquals(ManagementApprovalStatus::APPROVED, $this->closingRequest->status);
    }
    public function test_accept_noWaitingStatu_forbidden()
    {
        $this->closingRequest->status = ManagementApprovalStatus::REJECTED;
        $this->assertRegularExceptionThrowed(fn() => $this->accept(), 'Forbidden', 'unable to process concluded request');
    }
    public function test_accept_closeCustomerAssignment()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('closeAssignment');
        $this->accept();
    }
    
    //
    protected function reject()
    {
        $this->closingRequest->reject();
    }
    public function test_reject_setStatusApproved()
    {
        $this->reject();
        $this->assertEquals(ManagementApprovalStatus::REJECTED, $this->closingRequest->status);
    }
    public function test_reject_noWaitingStatu_forbidden()
    {
        $this->closingRequest->status = ManagementApprovalStatus::APPROVED;
        $this->assertRegularExceptionThrowed(fn() => $this->reject(), 'Forbidden', 'unable to process concluded request');
    }
    
    //
    protected function assertManageableByManager()
    {
        $this->closingRequest->assertManageableByManager($this->manager);
    }
    public function test_assertManageableByManager_unmanagedAssignedCustomer_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertManageableByManager(), 'Forbidden', 'unmanaged closing request');
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

class TestableClosingRequest extends ClosingRequest
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
