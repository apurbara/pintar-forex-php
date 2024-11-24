<?php

namespace Manager\Domain\Model\Manager\Sales\StrikingAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class ClosingRequestTest extends TestBase
{
    protected $closingRequest;
    protected $strikingAssignment;
    //
    protected $remark = 'remark';
    //
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->closingRequest = new TestableClosingRequest();
        $this->strikingAssignment = $this->buildMockOfClass(StrikingAssignment::class);
        $this->closingRequest->strikingAssignment = $this->strikingAssignment;
        //
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
    //
    protected function createClosingRequestData()
    {
        return (new ClosingRequestData())
                ->setRemark($this->remark);
    }
    
    //
    protected function accept()
    {
        $this->closingRequest->accept($this->createClosingRequestData());
    }
    public function test_accept_updateProperties()
    {
        $this->accept();
        $this->assertEquals(ManagementApprovalStatus::APPROVED, $this->closingRequest->status);
        $this->assertSame($this->remark, $this->closingRequest->remark);
    }
    public function test_accept_noWaitingStatu_forbidden()
    {
        $this->closingRequest->status = ManagementApprovalStatus::REJECTED;
        $this->assertRegularExceptionThrowed(fn() => $this->accept(), 'Forbidden', 'unable to process concluded request');
    }
    public function test_accept_closeStrikingAssignment()
    {
        $this->strikingAssignment->expects($this->once())
                ->method('closeAssignment');
        $this->accept();
    }
    
    //
    protected function reject()
    {
        $this->closingRequest->reject($this->createClosingRequestData());
    }
    public function test_reject_updateProperties()
    {
        $this->reject();
        $this->assertEquals(ManagementApprovalStatus::REJECTED, $this->closingRequest->status);
        $this->assertSame($this->remark, $this->closingRequest->remark);
    }
    public function test_reject_noWaitingStatu_forbidden()
    {
        $this->closingRequest->status = ManagementApprovalStatus::APPROVED;
        $this->assertRegularExceptionThrowed(fn() => $this->reject(), 'Forbidden', 'unable to process concluded request');
    }
    
    //
    protected function assertBelongsToManager()
    {
        $this->closingRequest->assertBelongsToManager($this->manager);
    }
    public function test_assertBelongsToManager_strikingAssignmentDoesNotBelongsToManager_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToManager(), 'Forbidden', 'closing request does not belongs to manager');
    }
    public function test_assertBelongsToManager_strikingAssignmentBelongsToManager_void()
    {
        $this->strikingAssignment->expects($this->once())
                ->method('belongsToManager')
                ->with($this->manager)
                ->willReturn(true);
        $this->assertBelongsToManager();
        $this->markAsSuccess();
    }
    
}

class TestableClosingRequest extends ClosingRequest
{
    public StrikingAssignment $strikingAssignment;
    public string $id;
    public ManagementApprovalStatus $status;
    public ?string $remark;
    
    function __construct()
    {
        parent::__construct();
        $this->status = ManagementApprovalStatus::WAITING_FOR_APPROVAL;
    }
}
