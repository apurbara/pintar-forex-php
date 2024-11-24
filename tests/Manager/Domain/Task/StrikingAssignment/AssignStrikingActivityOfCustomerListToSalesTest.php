<?php

namespace Manager\Domain\Task\StrikingAssignment;

use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Task\CustomerAssignment\AssignCustomerListToSalesPayload;
use Manager\Domain\Task\CustomerAssignment\CustomerAssignmentDistributionServiceInterface;
use Manager\Domain\Task\Dependency\CustomerRepository;
use Manager\Domain\Task\Sales\SalesRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Manager\Domain\Task\ManagerTaskTestBase;

class AssignStrikingActivityOfCustomerListToSalesTest extends ManagerTaskTestBase
{

    protected MockObject $salesRepository, $customerRepository, $customerAssignmentDistributionService;
    protected MockObject $salesOne, $salesTwo, $customer, $otherCustomer;
    protected string $salesOneId = 'salesOneId', $salesTwoId = 'salesTwoId', $customerId = 'customerId', $otherCustomerId = 'otherCustomerId';
    protected $dispatcher;
    protected $task;
    protected $payload, $secondCustomerId;
    protected $secondCustomer;
    protected $strikingAssignmentIdTwo = 'strikingAssignmentIdTwo';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareStrikingAssignmentDependency();
        $this->prepareCustomerJourneyDependency();
        //
        $this->customerRepository = $this->buildMockOfInterface(CustomerRepository::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->otherCustomer = $this->buildMockOfClass(Customer::class);
        $this->customerRepository->expects($this->any())
                ->method('ofId')
                ->willReturnCallback(fn($customerId) => match ($customerId) {
                            $this->customerId => $this->customer,
                            $this->otherCustomerId => $this->otherCustomer,
                        });
        //
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->salesOne = $this->buildMockOfClass(Sales::class);
        $this->salesTwo = $this->buildMockOfClass(Sales::class);
        $this->salesRepository->expects($this->any())
                ->method('ofId')
                ->willReturnCallback(fn($salesId) => match ($salesId) {
                            $this->salesOneId => $this->salesOne,
                            $this->salesTwoId => $this->salesTwo,
                        });
        //
        $this->customerAssignmentDistributionService = $this->buildMockOfInterface(CustomerAssignmentDistributionServiceInterface::class);
        
        $this->task = new AssignStrikingActivityOfCustomerListToSales(
                $this->strikingAssignmentRepository, $this->salesRepository, $this->customerRepository,
                $this->customerAssignmentDistributionService, $this->customerJourneyRepository);
        $this->payload = (new AssignCustomerListToSalesPayload)
                ->addSales($this->salesOneId)
                ->addSales($this->salesTwoId)
                ->addCustomer($this->customerId)
                ->addCustomer($this->otherCustomerId);
        //
    }

    //
    protected function execute()
    {
        $this->customerJourneyRepository->expects($this->any())
                ->method('anInitialCustomerJourney')
                ->willReturn($this->customerJourney);
        $this->strikingAssignmentRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturnOnConsecutiveCalls($this->strikingAssignmentId, $this->strikingAssignmentIdTwo);
        $this->customerAssignmentDistributionService->expects($this->any())
                ->method('getTopPrioritySales')
                ->willReturnOnConsecutiveCalls($this->salesOne, $this->salesTwo);
        $this->task->executeByManager($this->manager, $this->payload);
    }

    public function test_execute_addCustomerAssignnmentsToRepository()
    {
        $this->customerAssignmentDistributionService->expects($this->exactly(2))
                ->method('getTopPrioritySales')
                ->willReturnOnConsecutiveCalls($this->salesOne, $this->salesTwo);
        $this->strikingAssignmentRepository->expects($this->exactly(2))
                ->method('add');
        $this->execute();
    }

    public function test_execute_registerSalesToCalculatorService()
    {
        $this->customerAssignmentDistributionService->expects($this->exactly(2))
                ->method('registerSales');
        $this->execute();
    }
    public function test_execute_noPrioritySalesFromService()
    {
        $this->customerAssignmentDistributionService->expects($this->any())
                ->method('getTopPrioritySales')
                ->willReturn(null);
        $this->strikingAssignmentRepository->expects($this->never())
                ->method('add');
        $this->execute();
        $this->markAsSuccess();
    }
    public function test_execute_noInitialCustomerJourney()
    {
        $this->customerJourneyRepository->expects($this->once())
                ->method('anInitialCustomerJourney')
                ->willReturn(null);
        $this->execute();
        $this->markAsSuccess();
    }
    public function test_execute_assertSalesBelongsToManager()
    {
        $this->salesOne->expects($this->once())
                ->method('assertBelongsToManager');
        $this->salesTwo->expects($this->once())
                ->method('assertBelongsToManager');
        $this->execute();
    }
}
