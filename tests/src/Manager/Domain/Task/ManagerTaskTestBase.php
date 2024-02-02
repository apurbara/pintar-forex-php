<?php

namespace Tests\src\Manager\Domain\Task;

use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\CustomerJourney;
use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;
use Manager\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Manager\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Manager\Domain\Task\Customer\CustomerRepository;
use Manager\Domain\Task\CustomerJourney\CustomerJourneyRepository;
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
    
    //
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
    
    //
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
    
    //
    protected MockObject $assignedCustomerRepository;
    protected MockObject $assignedCustomer;
    protected string $assignedCustomerId = 'assignedCustomerId';
    protected function prepareAssignedCustomerDependency()
    {
        $this->assignedCustomerRepository = $this->buildMockOfInterface(AssignedCustomerRepository::class);
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
//        $this->assignedCustomerRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->assignedCustomerId)
//                ->willReturn($this->assignedCustomer);
    }
    
    //
    protected MockObject $customerRepository;
    protected MockObject $customer;
    protected string $customerId = 'customerId';
    protected function prepareCustomerDependency()
    {
        $this->customerRepository = $this->buildMockOfInterface(CustomerRepository::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerId)
                ->willReturn($this->customer);
    }
    
    //
    protected MockObject $customerJourneyRepository;
    protected MockObject $customerJourney;
    protected string $customerJourneyId = 'customerJourneyId';
    protected function prepareCustomerJourneyDependency()
    {
        $this->customerJourneyRepository = $this->buildMockOfInterface(CustomerJourneyRepository::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
    }
    
    //
    protected MockObject $salesRepository;
    protected MockObject $sales;
    protected string $salesId = 'salesId';
    protected function prepareSalesDependency()
    {
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->salesRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesId)
                ->willReturn($this->sales);
    }
    
}
