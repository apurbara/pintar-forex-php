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
use Shared\Domain\Enum\CustomerStatus;
use Tests\TestBase;

class CustomerTest extends TestBase
{
    protected $city;
    protected $customer;
    protected $verificationReport;
    //
    protected $id = 'newId', $name = 'new customer name', $bio = 'new customer bio', $email = 'newcustomer@email.org', $phone = '+6281324312123', $source = 'wa-forex-bdg';
    protected $rating = 4;
    //
    protected $customerVerification, $verificationReportData;
    protected $customerVerificationTwo;
    protected $allActiveCustomerVerifications;

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
        $this->customerVerificationTwo = $this->buildMockOfClass(CustomerVerification::class);
        //
        $this->allActiveCustomerVerifications = [$this->customerVerification, $this->customerVerificationTwo];
    }
    
    //
    protected function createData()
    {
        return (new CustomerData())
                ->setName($this->name)
                ->setBio($this->bio)
                ->setEmail($this->email)
                ->setPhone($this->phone)
                ->setSource($this->source);
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
        $this->assertSame($this->bio, $this->customer->bio);
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
    public function test_update_assertCityActive()
    {
        $this->city->expects($this->once())
                ->method('assertActive');
        $this->update();
    }
    public function test_update_emptyCity()
    {
        $this->city = null;
        $this->update();
        $this->markAsSuccess();
    }
    
    //
    protected function updateRating()
    {
        $this->customer->updateRating($this->rating);
    }
    public function test_updateRating_setRating()
    {
        $this->updateRating();
        $this->assertSame($this->rating, $this->customer->rating);
    }
    public function test_updateRating_ratingBiggerThan5_badRequest()
    {
        $this->rating = 6;
        $this->assertRegularExceptionThrowed(fn() => $this->updateRating(), 'Bad Request', 'invalid rating value');
    }
    public function test_updateRating_ratingLessThan5_badRequest()
    {
        $this->rating = -2;
        $this->assertRegularExceptionThrowed(fn() => $this->updateRating(), 'Bad Request', 'invalid rating value');
    }
    
    //
    protected function recycle()
    {
        $this->customer->recycle();
    }
    public function test_markAsInvalid_setStatusInvalid()
    {
        $this->recycle();
        $this->assertEquals(CustomerStatus::RECYCLED, $this->customer->status);
    }
    public function test_markAsInvalid_notNewCustomer_forbidden()
    {
        $this->customer->status = CustomerStatus::FACT_FINDING_REQUIRED;
        $this->assertRegularExceptionThrowed(fn() => $this->recycle(), 'Forbidden', 'unable to invalidate customer');
    }
    public function test_markAsInvalid_inavlidCustomer()
    {
        $this->customer->status = CustomerStatus::RECYCLED;
        $this->recycle();
        $this->markAsSuccess();
    }
    
    //
    protected function validate()
    {
        $this->customer->validate();
    }
    public function test_markValidationComplete_setStatusFactFindingRequired()
    {
        $this->validate();
        $this->assertEquals(CustomerStatus::FACT_FINDING_REQUIRED, $this->customer->status);
    }
    public function test_markValidationComplete_notNewCustomer_forbidden()
    {
        $this->customer->status = CustomerStatus::FACT_FINDING_REQUIRED;
        $this->assertRegularExceptionThrowed(fn() => $this->validate(), 'Forbidden', 'unable to validate customer');
    }
    public function test_markValidationComplete_inavlidCustomer()
    {
        $this->customer->status = CustomerStatus::RECYCLED;
        $this->validate();
        $this->markAsSuccess();
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
    public function test_submitVerificationReport_setPayloadId()
    {
        $this->verificationReport->expects($this->once())
                ->method('associateWithCustomerVerification')
                ->with($this->customerVerification)
                ->willReturn(true);
        $this->submitVerificationReport();
        $this->assertNotNull($this->verificationReportData->id);
    }

    //
    protected function markVerificationComplete()
    {
        $this->verificationReport->expects($this->any())
                ->method('associateWithCustomerVerification')
                ->willReturn(true);
        $this->customer->markVerificationComplete($this->allActiveCustomerVerifications);
    }
    public function test_markVerificationComplete_setStatusStrikingRequired()
    {
        $this->customer->status = CustomerStatus::FACT_FINDING_REQUIRED;
        $this->markVerificationComplete();
        $this->assertEquals(CustomerStatus::STRIKING_REQUIRED, $this->customer->status);
    }
    public function test_markVerificationComplete_nonFactFindingState_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->markVerificationComplete(), 'Forbidden', 'unable to set customer to striking phase');
    }
    public function test_markVerificationComplete_noVerificationReport_expectedResult()
    {
        $this->customer->status = CustomerStatus::FACT_FINDING_REQUIRED;
        $this->verificationReport->expects($this->exactly(2))
                ->method('associateWithCustomerVerification')
                ->willReturnOnConsecutiveCalls(true, false);
        $this->assertRegularExceptionThrowed(fn() => $this->markVerificationComplete(), 'Forbidden', 'incomplete verfication report');
    }
    
}

class TestableCustomer extends Customer
{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public CustomerStatus $status;
    public string $name;
    public ?string $bio;
    public ?string $email;
    public string $phone = '08123123123';
    public ?string $source = 'source';
    public ?int $rating = 3;
    public ?City $city;
    public Collection $verificationReports;
    
    public function __construct()
    {
        parent::__construct();
        $this->status = CustomerStatus::NEW;
    }
}
