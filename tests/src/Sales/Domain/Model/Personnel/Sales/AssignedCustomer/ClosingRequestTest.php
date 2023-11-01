<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use DateTimeImmutable;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\TestBase;

class ClosingRequestTest extends TestBase
{
    protected $assignedCustomer;
    protected $closingRequest;
    //
    protected $id = 'newId', $transacionValue = 35000000, $note = 'new note';
    //
    protected $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        $data = (new ClosingRequestData(15000000, 'note'))->setId('id');
        $this->closingRequest = new TestableClosingRequest($this->assignedCustomer, $data);
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
        return new TestableClosingRequest($this->assignedCustomer, $this->createData());
    }
    public function test_constuct_setProperties()
    {
        $closingRequest = $this->construct();
        $this->assertSame($this->assignedCustomer, $closingRequest->assignedCustomer);
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
    public function test_assertManageableBySales_assertAssignedCustomerBelongsToSales()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('assertBelongsToSales')
                ->with($this->sales);
        $this->assertManageableBySales();
    }
    
    //
    protected function isOngoing()
    {
        return $this->closingRequest->isOngoing();
    }
    public function test_isOngoing_ongoingRequest_returnTrue()
    {
        $this->assertTrue($this->isOngoing());
    }
    public function test_isOngoing_concludedRequest_returnFalse()
    {
        $this->closingRequest->status = ManagementApprovalStatus::REJECTED;
        $this->assertFalse($this->isOngoing());
    }
}

class TestableClosingRequest extends ClosingRequest
{
    public AssignedCustomer $assignedCustomer;
    public string $id;
    public DateTimeImmutable $createdTime;
    public ManagementApprovalStatus $status;
    public int $transactionValue;
    public ?string $note;
}
