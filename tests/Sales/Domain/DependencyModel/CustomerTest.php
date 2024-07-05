<?php

namespace Sales\Domain\DependencyModel;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\Customer\VerificationReport;
use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\DependencyModel\Province\City;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $city;
    protected $customer;
    protected $verificationReport;
    //
    protected $id = 'newId', $name = 'new customer name', $email = 'newcustomer@email.org', $phone = '+6281324312123', $source = 'wa-forex-bdg';
    //
    protected $customerVerification, $verificationReportData;


    protected function setUp(): void
    {
        parent::setUp();
        $this->city = $this->buildMockOfClass(City::class);
        
        $data = (new CustomerData())
                ->setName('customer')
                ->setEmail('customer@email.org')
                ->setPhone('08942342343')
                ->setSource('group');
        $this->customer = new TestableCustomer($this->city, 'id', $data);
        
        $this->verificationReport = $this->buildMockOfClass(VerificationReport::class);
        $this->customer->verificationReports = new ArrayCollection();
        $this->customer->verificationReports->add($this->verificationReport);
        //
        $this->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        $this->verificationReportData = (new VerificationReportData())->setNote('note');
    }
    
    //
    protected function createData()
    {
        return (new CustomerData())
                ->setName($this->name)
                ->setEmail($this->email)
                ->setPhone($this->phone)
                ->setSource($this->source);
    }
    
    //
    protected function construct()
    {
        return new TestableCustomer($this->city, $this->id, $this->createData());
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
        $this->assertSame($this->city, $customer->city);
        $this->assertSame($this->source, $customer->source);
    }
    public function test_construct_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'customer name is mandatory');
    }
    public function test_construct_invalidMailFormat_badRequest()
    {
        $this->email = 'bad mail format';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'customer email must be in valid email address format');
    }
    public function test_construct_emptyMail_200()
    {
        $this->email = '';
        $this->construct();
        $this->markAsSuccess();
    }
    public function test_construct_invalidPhoneFormat_badRequest()
    {
        $this->phone = 'bad phone format';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'customer phone is mandatory and must be in valid phone format');
    }
    public function test_construct_emptySource()
    {
        $this->source = null;
        $this->construct();
        $this->markAsSuccess();
    }
    public function test_construct_assertCityActive()
    {
        $this->city->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    
    //
    protected function update()
    {
        $this->customer->update($this->city, $this->createData());
    }
    public function test_update_updateCityAndProperties()
    {
        $this->customer->city = $this->buildMockOfClass(City::class);
        $this->update();
        $this->assertSame($this->city, $this->customer->city);
    }
    public function test_update_updateProperties()
    {
        $this->update();
        $this->assertSame($this->name, $this->customer->name);
        $this->assertSame($this->email, $this->customer->email);
    }
    public function test_update_preventUpdatePhone()
    {
        $this->update();
        $this->assertNotSame($this->phone, $this->customer->phone);
    }
    public function test_update_preventUpdateSource()
    {
        $this->update();
        $this->assertNotSame($this->phone, $this->customer->phone);
        $this->assertNotSame($this->source, $this->customer->source);
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
    public ?string $email;
    public string $phone;
    public ?string $source;
    public City $city;
    public Collection $verificationReports;
}
