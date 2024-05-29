<?php

namespace Company\Domain\Model\Personnel\Sales\CustomerAssignment;

use Company\Domain\Model\Personnel\Sales\CustomerAssignment;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class ClosingRequestTest extends TestBase
{
    protected $closingRequest;
    protected $customerAssignment;
    //
    protected $remark = 'remark';

    protected function setUp(): void
    {
        parent::setUp();
        $this->closingRequest = new TestableClosingRequest();
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->closingRequest->customerAssignment = $this->customerAssignment;
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
    public function test_accept_closeCustomerAssignment()
    {
        $this->customerAssignment->expects($this->once())
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
    
}

class TestableClosingRequest extends ClosingRequest
{
    public CustomerAssignment $customerAssignment;
    public string $id;
    public ManagementApprovalStatus $status;
    public ?string $remark;
    
    function __construct()
    {
        parent::__construct();
        $this->status = ManagementApprovalStatus::WAITING_FOR_APPROVAL;
    }
}
