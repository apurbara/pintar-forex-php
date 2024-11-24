<?php

namespace Sales\Domain\Model\Sales\StrikingAssignment;

use DateTimeImmutable;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class ClosingRequestTest extends TestBase
{
    protected $strikingAssignment;
    protected $closingRequest;
    //
    protected $id = 'newId', $transacionValue = 35000000, $note = 'new note';
    //
    protected $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strikingAssignment = $this->buildMockOfClass(StrikingAssignment::class);
        $data = (new ClosingRequestData(15000000, 'note'))->setId('id');
        $this->closingRequest = new TestableClosingRequest($this->strikingAssignment, 'id', $data);
        //
        $this->sales = $this->buildMockOfClass(Sales::class);
    }
    
    //
    protected function createData()
    {
        return (new ClosingRequestData($this->transacionValue, $this->note))->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableClosingRequest($this->strikingAssignment, $this->id, $this->createData());
    }
    public function test_constuct_setProperties()
    {
        $closingRequest = $this->construct();
        $this->assertSame($this->strikingAssignment, $closingRequest->strikingAssignment);
        $this->assertSame($this->id, $closingRequest->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($closingRequest->createdTime);
        $this->assertEquals(ManagementApprovalStatus::WAITING_FOR_APPROVAL, $closingRequest->status);
        $this->assertSame($this->transacionValue, $closingRequest->transactionValue);
        $this->assertSame($this->note, $closingRequest->note);
    }
    public function test_construct_emptyTransactionValue_badRequest()
    {
        $this->transacionValue = 0;
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'transaction value is mandatory');
    }
    
    //
    protected function update()
    {
        $this->closingRequest->update($this->createData());
    }
    public function test_update_setProperties()
    {
        $this->update();
        $this->assertSame($this->transacionValue, $this->closingRequest->transactionValue);
        $this->assertSame($this->note, $this->closingRequest->note);
    }
    public function test_update_emptyTransactionValue_badRequest()
    {
        $this->transacionValue = 0;
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'transaction value is mandatory');
    }
    public function test_update_notOngoingRequest_forbidden()
    {
        $this->closingRequest->status = ManagementApprovalStatus::REJECTED;
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Forbidden', 'request already concluded');
    }
    
    //
    protected function assertManageableBySales()
    {
        $this->closingRequest->assertManageableBySales($this->sales);
    }
    public function test_assertManageableBySales_strikingAssignmentDoesNotBelongsToSales_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertManageableBySales(), 'Forbidden', 'unmanaged closing request');
    }
    public function test_assertManageableBySales_strikingAssignmentBelongsToSales_void()
    {
        $this->strikingAssignment->expects($this->once())
                ->method('isBelongsToSales')
                ->with($this->sales)
                ->willReturn(true);
        $this->assertManageableBySales();
    }
}

class TestableClosingRequest extends ClosingRequest
{
    public StrikingAssignment $strikingAssignment;
    public string $id;
    public DateTimeImmutable $createdTime;
    public ManagementApprovalStatus $status;
    public int $transactionValue;
    public ?string $note;
}
