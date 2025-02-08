<?php

namespace Company\Domain\Model\Manager\Sales\FactFindingAssignment;

use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use DateTimeImmutable;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class ClosingRequestByFactFinderTest extends TestBase
{

    protected $closingRequestByFactFinder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->closingRequestByFactFinder = new TestableClosingRequestByFactFinder();
    }
    
    //
    protected function cancelBySystem()
    {
        $this->closingRequestByFactFinder->cancelBySystem();
    }
    public function test_cancelBySystem_setCancelled()
    {
        $this->cancelBySystem();
        $this->assertEquals(ManagementApprovalStatus::CANCELLED_BY_SYSTEM, $this->closingRequestByFactFinder->status);
    }
    public function test_cancelBySytem_alreadyConcluded_NOP()
    {
        $this->closingRequestByFactFinder->status = ManagementApprovalStatus::REJECTED;
        $this->cancelBySystem();
        $this->assertEquals(ManagementApprovalStatus::REJECTED, $this->closingRequestByFactFinder->status);
    }


}

class TestableClosingRequestByFactFinder extends ClosingRequestByFactFinder
{

    public FactFindingAssignment $factFindingAssignment;
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
