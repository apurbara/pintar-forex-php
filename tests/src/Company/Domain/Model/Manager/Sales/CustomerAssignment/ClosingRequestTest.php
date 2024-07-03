<?php

namespace Company\Domain\Model\Manager\Sales\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class ClosingRequestTest extends TestBase
{
    protected $closingRequest;
    protected $customerAssignment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->closingRequest = new TestableClosingRequest();
    }
    
    //
    protected function cancelBySystem()
    {
        $this->closingRequest->cancelBySystem();
    }
    public function test_cancelBySystem_setStatusCancelledBySystem()
    {
        $this->cancelBySystem();
        $this->assertEquals(ManagementApprovalStatus::CANCELLED_BY_SYSTEM, $this->closingRequest->status);
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
