<?php

namespace Sales\Domain\Model\AreaStructure\Area;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReportData;
use Sales\Domain\Model\CustomerVerification;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $area;
    protected $customer;
    protected $verificationReport;
    //
    protected $id = 'newId', $name = 'new customer name', $email = 'newcustomer@email.org', $phone = '+6281324312123';
    //
    protected $customerVerification, $verificationReportData;


    protected function setUp(): void
    {
        parent::setUp();
        $this->area = $this->buildMockOfClass(Area::class);
        
        $data = (new CustomerData('name', 'customer@email.org', '08932324234'))->setId('id');
        $this->customer = new TestableCustomer($this->area, $data);
        
        $this->verificationReport = $this->buildMockOfClass(VerificationReport::class);
        $this->customer->verificationReports = new ArrayCollection();
        $this->customer->verificationReports->add($this->verificationReport);
        //
        $this->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        $this->verificationReportData = new VerificationReportData('note');
    }
    
    //
    protected function createData()
    {
        return (new CustomerData($this->name, $this->email, $this->phone))->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableCustomer($this->area, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $customer = $this->construct();
        $this->assertSame($this->id, $customer->id);
        $this->assertfalse($customer->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($customer->createdTime);
        $this->assertSame($this->name, $customer->name);
        $this->assertSame($this->email, $customer->email);
        $this->assertSame($this->phone, $customer->phone);
        $this->assertSame($this->area, $customer->area);
    }
    public function test_construct_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'customer name is mandatory');
    }
    public function test_construct_invalidMailFormat_badRequest()
    {
        $this->email = 'bad mail format';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'customer email is mandatory and must be in valid email address format');
    }
    public function test_construct_invalidPhoneFormat_badRequest()
    {
        $this->phone = 'bad phone format';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'customer phone is mandatory and must be in valid phone format');
    }
    
    //
    protected function submitVerificationReport()
    {
        $this->customer->submitVerificationReport($this->customerVerification, $this->verificationReportData);
    }
    public function test_submitVerificationReport_addVerificationReportToCollection()
    {
        $this->submitVerificationReport();
        $this->assertEquals(2, $this->customer->verificationReports->count());
        $this->assertInstanceOf(VerificationReport::class, $this->customer->verificationReports->last());
    }
    public function test_submitVerificationReport_setVerificationReportDataId()
    {
        $this->submitVerificationReport();
        $this->assertNotNull($this->verificationReportData->id);
    }
    public function test_submitVerificationReport_hasReportAssociateWithCustomerVerification_updateCorrespondingReport()
    {
        $this->verificationReport->expects($this->once())
                ->method('associateWithCustomerVerification')
                ->with($this->customerVerification)
                ->willReturn(true);
        $this->verificationReport->expects($this->once())
                ->method('update')
                ->with($this->verificationReportData);
        $this->submitVerificationReport();
    }
    public function test_submitVerificationReport_hasReportAssociateWithCustomerVerification_preventAddNewReport()
    {
        $this->verificationReport->expects($this->once())
                ->method('associateWithCustomerVerification')
                ->with($this->customerVerification)
                ->willReturn(true);
        $this->submitVerificationReport();
        $this->assertEquals(1, $this->customer->verificationReports->count());
    }
    
}

class TestableCustomer extends Customer
{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public string $name;
    public string $email;
    public string $phone;
    public Area $area;
    public Collection $verificationReports;
}
