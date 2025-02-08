<?php

namespace Sales\Domain\Model\Sales\FactFindingAssignment;

use DateTimeImmutable;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class ClosingRequestByFactFinderTest extends TestBase
{
    protected $factFindingAssignment;
    protected $closingRequestByFactFinder;
    //
    protected $id = 'newId', $transacionValue = 35000000, $note = 'new note';
    //
    protected $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factFindingAssignment = $this->buildMockOfClass(FactFindingAssignment::class);
        $data = (new ClosingRequestByFactFinderData(15000000, 'note'))->setId('id');
        $this->closingRequestByFactFinder = new TestableClosingRequestByFactFinder($this->factFindingAssignment, 'id', $data);
        //
        $this->sales = $this->buildMockOfClass(Sales::class);
    }
    
    //
    protected function createData()
    {
        return (new ClosingRequestByFactFinderData($this->transacionValue, $this->note))->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableClosingRequestByFactFinder($this->factFindingAssignment, $this->id, $this->createData());
    }
    public function test_constuct_setProperties()
    {
        $closingRequestByFactFinder = $this->construct();
        $this->assertSame($this->factFindingAssignment, $closingRequestByFactFinder->factFindingAssignment);
        $this->assertSame($this->id, $closingRequestByFactFinder->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($closingRequestByFactFinder->createdTime);
        $this->assertEquals(ManagementApprovalStatus::WAITING_FOR_APPROVAL, $closingRequestByFactFinder->status);
        $this->assertSame($this->transacionValue, $closingRequestByFactFinder->transactionValue);
        $this->assertSame($this->note, $closingRequestByFactFinder->note);
    }
    public function test_construct_emptyTransactionValue_badRequest()
    {
        $this->transacionValue = 0;
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'transaction value is mandatory');
    }
    
    //
    protected function update()
    {
        $this->closingRequestByFactFinder->update($this->createData());
    }
    public function test_update_setProperties()
    {
        $this->update();
        $this->assertSame($this->transacionValue, $this->closingRequestByFactFinder->transactionValue);
        $this->assertSame($this->note, $this->closingRequestByFactFinder->note);
    }
    public function test_update_emptyTransactionValue_badRequest()
    {
        $this->transacionValue = 0;
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'transaction value is mandatory');
    }
    public function test_update_notOngoingRequest_forbidden()
    {
        $this->closingRequestByFactFinder->status = ManagementApprovalStatus::REJECTED;
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Forbidden', 'request already concluded');
    }
    
    //
    protected function assertManageableBySales()
    {
        $this->closingRequestByFactFinder->assertManageableBySales($this->sales);
    }
    public function test_assertManageableBySales_factFindingAssignmentDoesNotBelongsToSales_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->assertManageableBySales(), 'Forbidden', 'unmanaged closing request');
    }
    public function test_assertManageableBySales_factFindingAssignmentBelongsToSales_void()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('isBelongsToSales')
                ->with($this->sales)
                ->willReturn(true);
        $this->assertManageableBySales();
    }
}

class TestableClosingRequestByFactFinder extends ClosingRequestByFactFinder
{
    public FactFindingAssignment $factFindingAssignment;
    public string $id;
    public DateTimeImmutable $createdTime;
    public ManagementApprovalStatus $status;
    public int $transactionValue;
    public ?string $note;
}
