<?php

namespace Manager\Domain\Task\AssignedCustomer;

use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Service\CustomerAssignmentDistributionCalculatorService;
use Manager\Domain\Task\Customer\CustomerRepository;
use Manager\Domain\Task\Sales\SalesRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Resources\Event\Dispatcher;
use Tests\src\Manager\Domain\Task\ManagerTaskTestBase;

class AssignCustomerListToSalesTest extends ManagerTaskTestBase
{

    protected MockObject $salesRepository, $customerRepository, $customerAssignmentDistributionCalculatorService;
    protected MockObject $salesOne, $salesTwo, $customer, $otherCustomer;
    protected string $salesOneId = 'salesOneId', $salesTwoId = 'salesTwoId', $customerId = 'customerId', $otherCustomerId = 'otherCustomerId';
    protected $dispatcher;
    protected $task;
    protected $payload, $secondCustomerId;
    protected $secondCustomer;
    protected $assignedCustomerIdTwo = 'assignedCustomerIdTwo';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAssignedCustomerDependency();
        $this->prepareCustomerJourneyDependency();
        $this->dispatcher = $this->buildMockOfClass(Dispatcher::class);
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
        $this->customerAssignmentDistributionCalculatorService = $this->buildMockOfClass(CustomerAssignmentDistributionCalculatorService::class);
        
        $this->task = new AssignCustomerListToSales(
                $this->assignedCustomerRepository, $this->salesRepository, $this->customerRepository,
                $this->customerJourneyRepository, $this->customerAssignmentDistributionCalculatorService,
                $this->dispatcher);
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
        $this->assignedCustomerRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturnOnConsecutiveCalls($this->assignedCustomerId, $this->assignedCustomerIdTwo);
        $this->customerAssignmentDistributionCalculatorService->expects($this->any())
                ->method('getTopPrioritySalesForCustomerDistribution')
                ->willReturnOnConsecutiveCalls($this->salesOne, $this->salesTwo);
        $this->customerAssignmentDistributionCalculatorService->expects($this->any())
                ->method('getSalesList')
                ->willReturn([$this->salesOne, $this->salesTwo]);
        $this->customerJourneyRepository->expects($this->any())
                ->method('anInitialCustomerJourney')
                ->willReturn($this->customerJourney);
        $this->task->executeByManager($this->manager, $this->payload);
    }

    public function test_execute_addCustomerAssignnmentsToRepository()
    {
        $this->salesOne->expects($this->once())
                ->method('receiveCustomerAssignment')
                ->with($this->assignedCustomerId, $this->customer, $this->customerJourney)
                ->willReturn($this->assignedCustomer);
        $this->salesTwo->expects($this->once())
                ->method('receiveCustomerAssignment')
                ->with($this->assignedCustomerIdTwo, $this->otherCustomer, $this->customerJourney)
                ->willReturn($this->buildMockOfClass(AssignedCustomer::class));
        $this->assignedCustomerRepository->expects($this->exactly(2))
                ->method('add');
        $this->execute();
    }

    public function test_execute_dispatchEventsInSales()
    {
        $this->dispatcher->expects($this->exactly(2))
                ->method('dispatchEventContainer');
        $this->execute();
    }

    public function test_execute_assertSalesManageableByManager()
    {
        $this->salesOne->expects($this->once())
                ->method('assertManageableByManager');
        $this->salesTwo->expects($this->once())
                ->method('assertManageableByManager');
        $this->execute();
    }
    public function test_execute_registerSalesToCalculatorService()
    {
        $this->customerAssignmentDistributionCalculatorService->expects($this->exactly(2))
                ->method('registerSales');
        $this->execute();
    }
    public function test_execute_noPrioritySalesFromService()
    {
        $this->customerAssignmentDistributionCalculatorService->expects($this->any())
                ->method('getTopPrioritySalesForCustomerDistribution')
                ->willReturn(null);
        $this->execute();
        $this->markAsSuccess();
    }
}
