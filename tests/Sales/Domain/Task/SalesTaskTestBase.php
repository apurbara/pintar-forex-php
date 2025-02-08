<?php

namespace Tests\Sales\Domain\Task;

use PHPUnit\Framework\MockObject\MockObject;
use Resources\Event\Dispatcher;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment\ClosingRequest;
use Sales\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Sales\Domain\Task\ClosingRequestByFactFinder\ClosingRequestByFactFinderRepository;
use Sales\Domain\Task\Dependency\CityRepository;
use Sales\Domain\Task\Dependency\CustomerJourneyRepository;
use Sales\Domain\Task\Dependency\CustomerRepository;
use Sales\Domain\Task\Dependency\CustomerVerificationRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use Sales\Domain\Task\FactFindingAssignment\FactFindingAssignmentRepository;
use Sales\Domain\Task\GreetingAssignment\GreetingAssignmentRepository;
use Sales\Domain\Task\SalesActivityReport\SalesActivityReportRepository;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\StrikingAssignment\StrikingAssignmentRepository;
use Tests\TestBase;

class SalesTaskTestBase extends TestBase
{
    protected MockObject $sales;
    protected string $salesId = 'salesId';
    protected MockObject $dispatcher;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->sales->expects($this->any())
                ->method('getId')
                ->willReturn($this->salesId);
        $this->dispatcher = $this->buildMockOfClass(Dispatcher::class);
    }
    
    protected MockObject $greetingAssignmentRepository;
    protected MockObject $greetingAssignment;
    protected string $greetingAssignmentId = 'greetingAssignmentId';
    protected function prepareGreetingAssignmentDependency()
    {
        $this->greetingAssignmentRepository = $this->buildMockOfInterface(GreetingAssignmentRepository::class);
        $this->greetingAssignment = $this->buildMockOfClass(GreetingAssignment::class);
        
        $this->greetingAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->greetingAssignmentId)
                ->willReturn($this->greetingAssignment);
    }
    
    protected MockObject $factFindingAssignmentRepository;
    protected MockObject $factFindingAssignment;
    protected string $factFindingAssignmentId = 'factFindingAssignmentId';
    protected function prepareFactFindingAssignmentDependency()
    {
        $this->factFindingAssignmentRepository = $this->buildMockOfInterface(FactFindingAssignmentRepository::class);
        $this->factFindingAssignment = $this->buildMockOfClass(FactFindingAssignment::class);
        
        $this->factFindingAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->factFindingAssignmentId)
                ->willReturn($this->factFindingAssignment);
    }
    
    protected MockObject $strikingAssignmentRepository;
    protected MockObject $strikingAssignment;
    protected string $strikingAssignmentId = 'strikingAssignmentId';
    protected function prepareStrikingAssignmentDependency()
    {
        $this->strikingAssignmentRepository = $this->buildMockOfInterface(StrikingAssignmentRepository::class);
        $this->strikingAssignment = $this->buildMockOfClass(StrikingAssignment::class);
        
        $this->strikingAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->strikingAssignmentId)
                ->willReturn($this->strikingAssignment);
    }
    
    protected MockObject $cityRepository;
    protected MockObject $city;
    protected string $cityId = 'cityId';
    protected function prepareCityDependency()
    {
        $this->cityRepository = $this->buildMockOfInterface(CityRepository::class);
        $this->city = $this->buildMockOfClass(City::class);
        
        $this->cityRepository->expects($this->any())
                ->method('ofId')
                ->with($this->cityId)
                ->willReturn($this->city);
    }
    
    protected MockObject $customerRepository;
    protected MockObject $customer;
    protected string $customerId = 'customerId';
    protected function prepareCustomerDependency()
    {
        $this->customerRepository = $this->buildMockOfInterface(CustomerRepository::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        
//        $this->customerRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->customerId)
//                ->willReturn($this->customer);
    }
    
    protected MockObject $salesActivityRepository;
    protected ?MockObject $salesActivity;
    protected string $salesActivityId = 'salesActivityId';
    protected function prepareSalesActivityDependency()
    {
        $this->salesActivityRepository = $this->buildMockOfInterface(SalesActivityRepository::class);
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        
        $this->salesActivityRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesActivityId)
                ->willReturn($this->salesActivity);
    }
    
    protected MockObject $salesActivityScheduleRepository;
    protected MockObject $salesActivitySchedule;
    protected string $salesActivityScheduleId = 'salesActivityScheduleId';
    protected function prepareSalesActivityScheduleDependency()
    {
        $this->salesActivityScheduleRepository = $this->buildMockOfInterface(SalesActivityScheduleRepository::class);
        $this->salesActivitySchedule = $this->buildMockOfClass(SalesActivitySchedule::class);
        $this->salesActivityScheduleRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesActivityScheduleId)
                ->willReturn($this->salesActivitySchedule);
    }
    
    protected MockObject $salesActivityReportRepository;
    protected MockObject $salesActivityReport;
    protected string $salesActivityReportId = 'salesActivityReportId';
    protected function prepareSalesActivityReportDependency()
    {
        $this->salesActivityReportRepository = $this->buildMockOfInterface(SalesActivityReportRepository::class);
        $this->salesActivityReport = $this->buildMockOfClass(SalesActivityReport::class);
        
//        $this->salesActivityReportRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->salesActivityReportId)
//                ->willReturn($this->salesActivityReport);
    }
    
    protected MockObject $customerVerificationRepository;
    protected MockObject $customerVerification;
    protected string $customerVerificationId = 'customerVerificationId';
    protected function prepareCustomerVerificationDependency()
    {
        $this->customerVerificationRepository = $this->buildMockOfInterface(CustomerVerificationRepository::class);
        $this->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        
        $this->customerVerificationRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerVerificationId)
                ->willReturn($this->customerVerification);
    }
    
    protected MockObject $closingRequestRepository;
    protected MockObject $closingRequest;
    protected string $closingRequestId = 'closingRequestId';
    protected function prepareClosingRequestDependency()
    {
        $this->closingRequestRepository = $this->buildMockOfInterface(ClosingRequestRepository::class);
        $this->closingRequest = $this->buildMockOfClass(ClosingRequest::class);
        
        $this->closingRequestRepository->expects($this->any())
                ->method('ofId')
                ->with($this->closingRequestId)
                ->willReturn($this->closingRequest);
    }
    
    protected MockObject $closingRequestByFactFinderRepository;
    protected MockObject $closingRequestByFactFinder;
    protected string $closingRequestByFactFinderId = 'closingRequestByFactFinderId';
    protected function prepareClosingRequestByFactFinderDependency()
    {
        $this->closingRequestByFactFinderRepository = $this->buildMockOfInterface(ClosingRequestByFactFinderRepository::class);
        $this->closingRequestByFactFinder = $this->buildMockOfClass(ClosingRequestByFactFinder::class);
        
        $this->closingRequestByFactFinderRepository->expects($this->any())
                ->method('ofId')
                ->with($this->closingRequestByFactFinderId)
                ->willReturn($this->closingRequestByFactFinder);
    }
    
    protected MockObject $customerJourneyRepository;
    protected MockObject $customerJourney;
    protected string $customerJourneyId = 'customerJourneyId';
    protected function prepareCustomerJourneyDependency()
    {
        $this->customerJourneyRepository = $this->buildMockOfInterface(CustomerJourneyRepository::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        
        $this->customerJourneyRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerJourneyId)
                ->willReturn($this->customerJourney);
    }
}
