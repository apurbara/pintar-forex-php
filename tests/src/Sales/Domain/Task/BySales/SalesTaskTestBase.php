<?php

namespace Tests\src\Sales\Domain\Task\BySales;

use PHPUnit\Framework\MockObject\MockObject;
use Resources\Event\Dispatcher;
use Sales\Domain\DependencyModel\AreaStructure\Area;
use Sales\Domain\DependencyModel\AreaStructure\Area\Customer;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequest;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Task\BySales\ClosingRequest\ClosingRequestRepository;
use Sales\Domain\Task\BySales\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\BySales\RecycleRequest\RecycleRequestRepository;
use Sales\Domain\Task\BySales\SalesActivityReport\SalesActivityReportRepository;
use Sales\Domain\Task\BySales\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\Dependency\AreaRepository;
use Sales\Domain\Task\Dependency\CustomerJourneyRepository;
use Sales\Domain\Task\Dependency\CustomerRepository;
use Sales\Domain\Task\Dependency\CustomerVerificationRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use Tests\TestBase;

class SalesTaskTestBase extends TestBase
{
    protected MockObject $sales;
    protected MockObject $dispatcher;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->dispatcher = $this->buildMockOfClass(Dispatcher::class);
    }
    
    protected MockObject $customerAssignmentRepository;
    protected MockObject $customerAssignment;
    protected string $customerAssignmentId = 'customerAssignmentId';
    protected function prepareCustomerAssignmentDependency()
    {
        $this->customerAssignmentRepository = $this->buildMockOfInterface(CustomerAssignmentRepository::class);
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        
        $this->customerAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerAssignmentId)
                ->willReturn($this->customerAssignment);
    }
    
    protected MockObject $areaRepository;
    protected MockObject $area;
    protected string $areaId = 'areaId';
    protected function prepareAreaDependency()
    {
        $this->areaRepository = $this->buildMockOfInterface(AreaRepository::class);
        $this->area = $this->buildMockOfClass(Area::class);
        
        $this->areaRepository->expects($this->any())
                ->method('ofId')
                ->with($this->areaId)
                ->willReturn($this->area);
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
    
    protected MockObject $recycleRequestRepository;
    protected MockObject $recycleRequest;
    protected string $recycleRequestId = 'recycleRequestId';
    protected function prepareRecycleRequestDependency()
    {
        $this->recycleRequestRepository = $this->buildMockOfInterface(RecycleRequestRepository::class);
        $this->recycleRequest = $this->buildMockOfClass(RecycleRequest::class);
        
        $this->recycleRequestRepository->expects($this->any())
                ->method('ofId')
                ->with($this->recycleRequestId)
                ->willReturn($this->recycleRequest);
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
