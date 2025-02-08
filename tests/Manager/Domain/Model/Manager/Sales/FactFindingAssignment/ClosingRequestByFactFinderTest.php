<?php

namespace Manager\Domain\Model\Manager\Sales\FactFindingAssignment;

use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class ClosingRequestByFactFinderTest extends TestBase
{
    protected $closingRequestByFactFinder;
    protected $factFindingAssignment;
    //
    protected $remark = 'remark';
    //
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->closingRequestByFactFinder = new TestableClosingRequestByFactFinder();
        $this->factFindingAssignment = $this->buildMockOfClass(FactFindingAssignment::class);
        $this->closingRequestByFactFinder->factFindingAssignment = $this->factFindingAssignment;
        //
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
    //
    protected function createClosingRequestByFactFinderData()
    {
        return (new ClosingRequestByFactFinderData())
                ->setRemark($this->remark);
    }
    
    //
    protected function accept()
    {
        $this->closingRequestByFactFinder->accept($this->createClosingRequestByFactFinderData());
    }
    public function test_accept_updateProperties()
    {
        $this->accept();
        $this->assertEquals(ManagementApprovalStatus::APPROVED, $this->closingRequestByFactFinder->status);
        $this->assertSame($this->remark, $this->closingRequestByFactFinder->remark);
    }
    public function test_accept_noWaitingStatu_forbidden()
    {
        $this->closingRequestByFactFinder->status = ManagementApprovalStatus::REJECTED;
        $this->assertRegularExceptionThrowed(fn() => $this->accept(), 'Forbidden', 'unable to process concluded request');
    }
    public function test_accept_closeFactFindingAssignment()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('closeAssignment');
        $this->accept();
    }
    
    //
    protected function reject()
    {
        $this->closingRequestByFactFinder->reject($this->createClosingRequestByFactFinderData());
    }
    public function test_reject_updateProperties()
    {
        $this->reject();
        $this->assertEquals(ManagementApprovalStatus::REJECTED, $this->closingRequestByFactFinder->status);
        $this->assertSame($this->remark, $this->closingRequestByFactFinder->remark);
    }
    public function test_reject_noWaitingStatu_forbidden()
    {
        $this->closingRequestByFactFinder->status = ManagementApprovalStatus::APPROVED;
        $this->assertRegularExceptionThrowed(fn() => $this->reject(), 'Forbidden', 'unable to process concluded request');
    }
    
    //
    protected function assertBelongsToManager()
    {
        $this->closingRequestByFactFinder->assertBelongsToManager($this->manager);
    }
    public function test_assertBelongsToManager_factFindingAssignmentDoesNotBelongsToManager_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToManager(), 'Forbidden', 'closing request does not belongs to manager');
    }
    public function test_assertBelongsToManager_factFindingAssignmentBelongsToManager_void()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('belongsToManager')
                ->with($this->manager)
                ->willReturn(true);
        $this->assertBelongsToManager();
        $this->markAsSuccess();
    }
    
}

class TestableClosingRequestByFactFinder extends ClosingRequestByFactFinder
{
    public FactFindingAssignment $factFindingAssignment;
    public string $id;
    public ManagementApprovalStatus $status;
    public ?string $remark;
    
    function __construct()
    {
        parent::__construct();
        $this->status = ManagementApprovalStatus::WAITING_FOR_APPROVAL;
    }
}
