<?php

namespace Company\Domain\Model\Manager\Sales\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use DateTimeImmutable;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class RecycleRequestTest extends TestBase
{

    protected $recycleRequest;

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
    public function test_cancelBySystem_setCancelled()
    {
        $this->cancelBySystem();
        $this->assertEquals(ManagementApprovalStatus::CANCELLED_BY_SYSTEM, $this->recycleRequest->status);
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
