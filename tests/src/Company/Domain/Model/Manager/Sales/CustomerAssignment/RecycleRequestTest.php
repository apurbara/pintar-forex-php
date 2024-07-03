<?php

namespace Company\Domain\Model\Manager\Sales\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class RecycleRequestTest extends TestBase
{
    protected $recycleRequest;
    protected $customerAssignment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recycleRequest = new TestableRecycleRequest();
    }
    
    //
    protected function cancelBySystem()
    {
        $this->recycleRequest->cancelBySystem();
    }
    public function test_cancelBySystem_setStatusCancelledBySystem()
    {
        $this->cancelBySystem();
        $this->assertEquals(ManagementApprovalStatus::CANCELLED_BY_SYSTEM, $this->recycleRequest->status);
    }
}

class TestableRecycleRequest extends RecycleRequest
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
