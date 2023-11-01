<?php

use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\Model\CustomerVerification;
use Tests\TestBase;

class VerificationReportTest extends TestBase
{
    protected $customer;
    protected $customerVerification;
    protected $verificationReport;
    //
    protected $id = 'newId', $note = 'new note';
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        
        $data = (new Customer\VerificationReportData('note'))->setId('id');
        $this->verificationReport = new TestableVerificationReport($this->customer, $this->customerVerification, $data);
    }
    
    //
    protected function createData()
    {
        return (new Customer\VerificationReportData($this->note))->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableVerificationReport($this->customer, $this->customerVerification, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $report = $this->construct();
        $this->assertSame($this->customer, $report->customer);
        $this->assertSame($this->customerVerification, $report->customerVerification);
        $this->assertSame($this->id, $report->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($report->createdTime);
        $this->assertSame($this-> note, $report->note);
    }
    public function test_construct_assertCustomerVerificationActive()
    {
        $this->customerVerification->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    
    //
    protected function update()
    {
        $this->verificationReport->update($this->createData());
    }
    public function test_update_updateProperties()
    {
        $this->update();
        $this->assertSame($this-> note, $this->verificationReport->note);
    }
    
    //
    protected function associateWithCustomerVerification()
    {
        return $this->verificationReport->associateWithCustomerVerification($this->customerVerification);
    }
    public function test_associateWithCustomerVerification_sameVerification_returnTrue()
    {
        $this->assertTrue($this->associateWithCustomerVerification());
    }
    public function test_associateWithCustomerVerification_diffVerification_returnFalse()
    {
        $this->verificationReport->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        $this->assertFalse($this->associateWithCustomerVerification());
    }
    
}

class TestableVerificationReport extends VerificationReport
{
    public Customer $customer;
    public CustomerVerification $customerVerification;
    public string $id;
    public DateTimeImmutable $createdTime;
    public ?string $note;
}
