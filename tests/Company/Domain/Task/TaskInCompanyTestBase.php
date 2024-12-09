<?php

namespace Tests\Company\Domain\Task;

use Company\Domain\Model\CommonSalesMetric;
use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\FactFinderMetric;
use Company\Domain\Model\GreeterMetric;
use Company\Domain\Model\Manager;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Model\Province;
use Company\Domain\Model\Province\City;
use Company\Domain\Model\SalesActivity;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesRank;
use Company\Domain\Task\City\CityRepository;
use Company\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Company\Domain\Task\CommonSalesMetric\CommonSalesMetricRepository;
use Company\Domain\Task\CompanyMetric\CompanyMetricRepository;
use Company\Domain\Task\Customer\CustomerRepository;
use Company\Domain\Task\CustomerAssignment\FactFindingAssignmentRepository;
use Company\Domain\Task\CustomerAssignment\GreetingAssignmentRepository;
use Company\Domain\Task\CustomerAssignment\StrikingAssignmentRepository;
use Company\Domain\Task\CustomerJourney\CustomerJourneyRepository;
use Company\Domain\Task\CustomerVerification\CustomerVerificationRepository;
use Company\Domain\Task\FactFinderMetric\FactFinderMetricRepository;
use Company\Domain\Task\GreeterMetric\GreeterMetricRepository;
use Company\Domain\Task\Manager\ManagerRepository;
use Company\Domain\Task\Province\ProvinceRepository;
use Company\Domain\Task\Sales\SalesRepository;
use Company\Domain\Task\SalesActivity\SalesActivityRepository;
use Company\Domain\Task\SalesPerformanceMetric\SalesPerformanceMetricRepository;
use Company\Domain\Task\SalesRank\SalesRankRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestBase;

class TaskInCompanyTestBase extends TestBase
{

    protected MockObject $managerRepository;
    protected MockObject $manager;
    protected string $managerId = 'managerId';

    protected function prepareManagerDependency(): void
    {
        $this->managerRepository = $this->buildMockOfInterface(ManagerRepository::class);
        $this->manager = $this->buildMockOfClass(Manager::class);
        //
        $this->managerRepository->expects($this->any())
                ->method('ofId')
                ->with($this->managerId)
                ->willReturn($this->manager);
    }

    protected MockObject $salesRepository;
    protected MockObject $sales;
    protected string $salesId = 'salesId';

    protected function prepareSalesDependency(): void
    {
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->sales = $this->buildMockOfClass(Sales::class);
        //
        $this->salesRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesId)
                ->willReturn($this->sales);
    }

    protected MockObject $customerVerificationRepository;
    protected MockObject $customerVerification;
    protected string $customerVerificationId = 'customerVerificationId';

    protected function prepareCustomerVerificationDependency(): void
    {
        $this->customerVerificationRepository = $this->buildMockOfInterface(CustomerVerificationRepository::class);
        $this->customerVerification = $this->buildMockOfClass(CustomerVerification::class);
        //
        $this->customerVerificationRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerVerificationId)
                ->willReturn($this->customerVerification);
    }

    protected MockObject $salesActivityRepository;
    protected MockObject $salesActivity;
    protected string $salesActivityId = 'salesActivityId';

    protected function prepareSalesActivityDependency(): void
    {
        $this->salesActivityRepository = $this->buildMockOfInterface(SalesActivityRepository::class);
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        //
        $this->salesActivityRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesActivityId)
                ->willReturn($this->salesActivity);
    }

    protected MockObject $customerJourneyRepository;
    protected MockObject $customerJourney;
    protected string $customerJourneyId = 'customerJourneyId';

    protected function prepareCustomerJourneyDependency(): void
    {
        $this->customerJourneyRepository = $this->buildMockOfInterface(CustomerJourneyRepository::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->customerJourneyRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerJourneyId)
                ->willReturn($this->customerJourney);
    }

    protected MockObject $customerRepository;
    protected MockObject $customer;
    protected string $customerId = 'customerId';

    protected function prepareCustomerDependency(): void
    {
        $this->customerRepository = $this->buildMockOfInterface(CustomerRepository::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        //
        $this->customerRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerId)
                ->willReturn($this->customer);
    }

    protected MockObject $greetingAssignmentRepository;
    protected MockObject $greetingAssignment;
    protected string $greetingAssignmentId = 'greetingAssignmentId';
    protected function prepareGreetingAssignmentDependency(): void
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
    protected function prepareFactFindingAssignmentDependency(): void
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
    protected function prepareStrikingAssignmentDependency(): void
    {
        $this->strikingAssignmentRepository = $this->buildMockOfInterface(StrikingAssignmentRepository::class);
        $this->strikingAssignment = $this->buildMockOfClass(StrikingAssignment::class);
        $this->strikingAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->strikingAssignmentId)
                ->willReturn($this->strikingAssignment);
    }

    protected MockObject $closingRequestRepository;
    protected MockObject $closingRequest;
    protected string $closingRequestId = 'closingRequestId';
    protected function prepareClosingRequestDependency(): void
    {
        $this->closingRequestRepository = $this->buildMockOfInterface(ClosingRequestRepository::class);
        $this->closingRequest = $this->buildMockOfClass(ClosingRequest::class);
        //
        $this->closingRequestRepository->expects($this->any())
                ->method('ofId')
                ->with($this->closingRequestId)
                ->willReturn($this->closingRequest);
    }

    protected MockObject $companyMetricRepository;
    protected MockObject $companyMetric;
    protected string $companyMetricId = 'companyMetricId';

    protected function prepareCompanyMetricDependency(): void
    {
        $this->companyMetricRepository = $this->buildMockOfInterface(CompanyMetricRepository::class);
        $this->companyMetric = $this->buildMockOfClass(CompanyMetric::class);
        //
        $this->companyMetricRepository->expects($this->any())
                ->method('ofId')
                ->with($this->companyMetricId)
                ->willReturn($this->companyMetric);
    }

    protected MockObject $salesRankRepository;
    protected MockObject $salesRank;
    protected string $salesRankId = 'salesRankId';

    protected function prepareSalesRankDependency(): void
    {
        $this->salesRankRepository = $this->buildMockOfInterface(SalesRankRepository::class);
        $this->salesRank = $this->buildMockOfClass(SalesRank::class);
        //
        $this->salesRankRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesRankId)
                ->willReturn($this->salesRank);
    }

    protected MockObject $commonSalesMetricRepository;
    protected MockObject $commonSalesMetric;
    protected string $commonSalesMetricId = 'commonSalesMetricId';

    protected function prepareCommonSalesMetricDependency(): void
    {
        $this->commonSalesMetricRepository = $this->buildMockOfInterface(CommonSalesMetricRepository::class);
        $this->commonSalesMetric = $this->buildMockOfClass(CommonSalesMetric::class);
        //
        $this->commonSalesMetricRepository->expects($this->any())
                ->method('ofId')
                ->with($this->commonSalesMetricId)
                ->willReturn($this->commonSalesMetric);
    }

    protected MockObject $salesPerformanceMetricRepository;
    protected MockObject $salesPerformanceMetric;
    protected string $salesPerformanceMetricId = 'salesPerformanceMetricId';

    protected function prepareSalesPerformanceMetricDependency(): void
    {
        $this->salesPerformanceMetricRepository = $this->buildMockOfInterface(SalesPerformanceMetricRepository::class);
        $this->salesPerformanceMetric = $this->buildMockOfClass(SalesPerformanceMetric::class);
        //
        $this->salesPerformanceMetricRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesPerformanceMetricId)
                ->willReturn($this->salesPerformanceMetric);
    }

    protected MockObject $provinceRepository;
    protected MockObject $province;
    protected string $provinceId = 'provinceId';

    protected function prepareProvinceDependency(): void
    {
        $this->provinceRepository = $this->buildMockOfInterface(ProvinceRepository::class);
        $this->province = $this->buildMockOfClass(Province::class);
        //
        $this->provinceRepository->expects($this->any())
                ->method('ofId')
                ->with($this->provinceId)
                ->willReturn($this->province);
    }

    protected MockObject $cityRepository;
    protected MockObject $city;
    protected string $cityId = 'cityId';

    protected function prepareCityDependency(): void
    {
        $this->cityRepository = $this->buildMockOfInterface(CityRepository::class);
        $this->city = $this->buildMockOfClass(City::class);
        //
        $this->cityRepository->expects($this->any())
                ->method('ofId')
                ->with($this->cityId)
                ->willReturn($this->city);
    }

    protected MockObject $greeterMetricRepository;
    protected MockObject $greeterMetric;
    protected string $greeterMetricId = 'greeterMetricId';

    protected function prepareGreeterMetricDependency(): void
    {
        $this->greeterMetricRepository = $this->buildMockOfInterface(GreeterMetricRepository::class);
        $this->greeterMetric = $this->buildMockOfClass(GreeterMetric::class);
        //
        $this->greeterMetricRepository->expects($this->any())
                ->method('ofId')
                ->with($this->greeterMetricId)
                ->willReturn($this->greeterMetric);
    }

    protected MockObject $factFinderMetricRepository;
    protected MockObject $factFinderMetric;
    protected string $factFinderMetricId = 'factFinderMetricId';

    protected function prepareFactFinderMetricDependency(): void
    {
        $this->factFinderMetricRepository = $this->buildMockOfInterface(FactFinderMetricRepository::class);
        $this->factFinderMetric = $this->buildMockOfClass(FactFinderMetric::class);
        //
        $this->factFinderMetricRepository->expects($this->any())
                ->method('ofId')
                ->with($this->factFinderMetricId)
                ->willReturn($this->factFinderMetric);
    }
}
