<?php

namespace Tests\Manager\Domain\Task;

use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use Manager\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Manager\Domain\Task\Dependency\CustomerJourneyRepository;
use Manager\Domain\Task\Dependency\CustomerRepository;
use Manager\Domain\Task\RecycleRequest\RecycleRequestRepository;
use Manager\Domain\Task\Sales\SalesRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestBase;

class ManagerTaskTestBase extends TestBase
{
    protected MockObject $manager;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
    
    protected MockObject $customerJourneyRepository, $customerJourney;
    protected string $customerJourneyId = 'customerJourneyId';
    protected function prepareCustomerJourneyDependency(): void
    {
        $this->customerJourneyRepository = $this->buildMockOfInterface(CustomerJourneyRepository::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
//        $this->customerJourneyRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->customerJourneyId)
//                ->willReturn($this->customerJourney);
    }
    
    protected MockObject $customerRepository, $customer;
    protected string $customerId = 'customerId';
    protected function prepareCustomerDependency(): void
    {
        $this->customerRepository = $this->buildMockOfInterface(CustomerRepository::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerId)
                ->willReturn($this->customer);
    }

    //
    protected MockObject $closingRequestRepository, $closingRequest;
    protected string $closingRequestId = 'closingRequestId';
    protected function prepareClosingRequestDependency(): void
    {
        $this->closingRequestRepository = $this->buildMockOfInterface(ClosingRequestRepository::class);
        $this->closingRequest = $this->buildMockOfClass(ClosingRequest::class);
        $this->closingRequestRepository->expects($this->any())
                ->method('ofId')
                ->with($this->closingRequestId)
                ->willReturn($this->closingRequest);
    }
    
    protected MockObject $recycleRequestRepository, $recycleRequest;
    protected string $recycleRequestId = 'recycleRequestId';
    protected function prepareRecycleRequestDependency(): void
    {
        $this->recycleRequestRepository = $this->buildMockOfInterface(RecycleRequestRepository::class);
        $this->recycleRequest = $this->buildMockOfClass(RecycleRequest::class);
        $this->recycleRequestRepository->expects($this->any())
                ->method('ofId')
                ->with($this->recycleRequestId)
                ->willReturn($this->recycleRequest);
    }
    
    protected MockObject $customerAssignmentRepository, $customerAssignment;
    protected string $customerAssignmentId = 'customerAssignmentId';
    protected function prepareCustomerAssignmentDependency(): void
    {
        $this->customerAssignmentRepository = $this->buildMockOfInterface(CustomerAssignmentRepository::class);
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        $this->customerAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerAssignmentId)
                ->willReturn($this->customerAssignment);
    }
    
    protected MockObject $salesRepository, $sales;
    protected string $salesId = 'salesId';
    protected function prepareSalesDependency(): void
    {
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->salesRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesId)
                ->willReturn($this->sales);
    }
}
