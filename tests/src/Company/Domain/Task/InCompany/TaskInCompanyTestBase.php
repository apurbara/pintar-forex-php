<?php

namespace Tests\src\Company\Domain\Task\InCompany;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\CommonSalesMetric;
use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\Manager;
use Company\Domain\Model\Sales;
use Company\Domain\Model\Sales\CustomerAssignment;
use Company\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Model\Sales\CustomerAssignment\RecycleRequest;
use Company\Domain\Model\SalesActivity;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesRank;
use Company\Domain\Task\InCompany\Area\AreaRepository;
use Company\Domain\Task\InCompany\AreaStructure\AreaStructureRepository;
use Company\Domain\Task\InCompany\ClosingRequest\ClosingRequestRepository;
use Company\Domain\Task\InCompany\CommonSalesMetric\CommonSalesMetricRepository;
use Company\Domain\Task\InCompany\CompanyMetric\CompanyMetricRepository;
use Company\Domain\Task\InCompany\Customer\CustomerRepository;
use Company\Domain\Task\InCompany\CustomerAssignment\CustomerAssignmentRepository;
use Company\Domain\Task\InCompany\CustomerJourney\CustomerJourneyRepository;
use Company\Domain\Task\InCompany\CustomerVerification\CustomerVerificationRepository;
use Company\Domain\Task\InCompany\Manager\ManagerRepository;
use Company\Domain\Task\InCompany\RecycleRequest\RecycleRequestRepository;
use Company\Domain\Task\InCompany\Sales\SalesRepository;
use Company\Domain\Task\InCompany\SalesActivity\SalesActivityRepository;
use Company\Domain\Task\InCompany\SalesPerformanceMetric\SalesPerformanceMetricRepository;
use Company\Domain\Task\InCompany\SalesRank\SalesRankRepository;
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

    protected MockObject $areaStructureRepository;
    protected MockObject $areaStructure;
    protected string $areaStructureId = 'areaStructureId';

    protected function prepareAreaStructureDependency(): void
    {
        $this->areaStructureRepository = $this->buildMockOfInterface(AreaStructureRepository::class);
        $this->areaStructure = $this->buildMockOfClass(AreaStructure::class);
        //
        $this->areaStructureRepository->expects($this->any())
                ->method('ofId')
                ->with($this->areaStructureId)
                ->willReturn($this->areaStructure);
    }

    protected MockObject $areaRepository;
    protected MockObject $area;
    protected string $areaId = 'areaId';

    protected function prepareAreaDependency(): void
    {
        $this->areaRepository = $this->buildMockOfInterface(AreaRepository::class);
        $this->area = $this->buildMockOfClass(Area::class);
        //
        $this->areaRepository->expects($this->any())
                ->method('ofId')
                ->with($this->areaId)
                ->willReturn($this->area);
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

    protected MockObject $customerAssignmentRepository;
    protected MockObject $customerAssignment;
    protected string $customerAssignmentId = 'customerAssignmentId';

    protected function prepareCustomerAssignmentDependency(): void
    {
        $this->customerAssignmentRepository = $this->buildMockOfInterface(CustomerAssignmentRepository::class);
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        //
//        $this->customerAssignmentRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->customerAssignmentId)
//                ->willReturn($this->customerAssignment);
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

    protected MockObject $recycleRequestRepository;
    protected MockObject $recycleRequest;
    protected string $recycleRequestId = 'recycleRequestId';

    protected function prepareRecycleRequestDependency(): void
    {
        $this->recycleRequestRepository = $this->buildMockOfInterface(RecycleRequestRepository::class);
        $this->recycleRequest = $this->buildMockOfClass(RecycleRequest::class);
        //
        $this->recycleRequestRepository->expects($this->any())
                ->method('ofId')
                ->with($this->recycleRequestId)
                ->willReturn($this->recycleRequest);
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
}
