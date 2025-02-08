<?php

namespace Tests\Manager\Domain\Task;

use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Manager\Domain\Model\Manager\Sales\GreetingAssignment;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;
use Manager\Domain\Task\ClosingRequest\ClosingRequestRepository;
use Manager\Domain\Task\ClosingRequestByFactFinder\ClosingRequestByFactFinderRepository;
use Manager\Domain\Task\Dependency\CustomerJourneyRepository;
use Manager\Domain\Task\Dependency\CustomerRepository;
use Manager\Domain\Task\FactFindingAssignment\FactFindingAssignmentRepository;
use Manager\Domain\Task\GreetingAssignment\GreetingAssignmentRepository;
use Manager\Domain\Task\Sales\SalesRepository;
use Manager\Domain\Task\StrikingAssignment\StrikingAssignmentRepository;
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

    //
    protected MockObject $closingRequestByFactFinderRepository, $closingRequestByFactFinder;
    protected string $closingRequestByFactFinderId = 'closingRequestByFactFinderId';
    protected function prepareClosingRequestByFactFinderDependency(): void
    {
        $this->closingRequestByFactFinderRepository = $this->buildMockOfInterface(ClosingRequestByFactFinderRepository::class);
        $this->closingRequestByFactFinder = $this->buildMockOfClass(ClosingRequestByFactFinder::class);
        $this->closingRequestByFactFinderRepository->expects($this->any())
                ->method('ofId')
                ->with($this->closingRequestByFactFinderId)
                ->willReturn($this->closingRequestByFactFinder);
    }
    
    protected MockObject $greetingAssignmentRepository, $greetingAssignment;
    protected string $greetingAssignmentId = 'greetingAssignmentId';
    protected function prepareGreetingAssignmentDependency(): void
    {
        $this->greetingAssignmentRepository = $this->buildMockOfInterface(GreetingAssignmentRepository::class);
        $this->greetingAssignment = $this->buildMockOfClass(GreetingAssignment::class);
//        $this->greetingAssignmentRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->greetingAssignmentId)
//                ->willReturn($this->greetingAssignment);
    }
    
    protected MockObject $factFindingAssignmentRepository, $factFindingAssignment;
    protected string $factFindingAssignmentId = 'factFindingAssignmentId';
    protected function prepareFactFindingAssignmentDependency(): void
    {
        $this->factFindingAssignmentRepository = $this->buildMockOfInterface(FactFindingAssignmentRepository::class);
        $this->factFindingAssignment = $this->buildMockOfClass(FactFindingAssignment::class);
//        $this->factFindingAssignmentRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->factFindingAssignmentId)
//                ->willReturn($this->factFindingAssignment);
    }
    
    protected MockObject $strikingAssignmentRepository, $strikingAssignment;
    protected string $strikingAssignmentId = 'strikingAssignmentId';
    protected function prepareStrikingAssignmentDependency(): void
    {
        $this->strikingAssignmentRepository = $this->buildMockOfInterface(StrikingAssignmentRepository::class);
        $this->strikingAssignment = $this->buildMockOfClass(StrikingAssignment::class);
//        $this->strikingAssignmentRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->strikingAssignmentId)
//                ->willReturn($this->strikingAssignment);
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
