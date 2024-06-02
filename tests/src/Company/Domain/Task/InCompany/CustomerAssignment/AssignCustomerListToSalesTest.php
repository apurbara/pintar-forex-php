<?php

namespace Company\Domain\Task\InCompany\CustomerAssignment;

use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\Sales;
use Company\Domain\Model\Sales\CustomerAssignment;
use Company\Domain\Task\InCompany\Customer\CustomerRepository;
use Company\Domain\Task\InCompany\Sales\SalesRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Resources\Event\Dispatcher;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AssignCustomerListToSalesTest extends TaskInCompanyTestBase
{

    protected MockObject $salesRepository, $customerRepository, $customerAssignmentDistributionService;
    protected MockObject $salesOne, $salesTwo, $customer, $otherCustomer;
    protected string $salesOneId = 'salesOneId', $salesTwoId = 'salesTwoId', $customerId = 'customerId', $otherCustomerId = 'otherCustomerId';
    protected $dispatcher;
    protected $task;
    protected $payload, $secondCustomerId;
    protected $secondCustomer;
    protected $customerAssignmentIdTwo = 'customerAssignmentIdTwo';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCustomerAssignmentDependency();
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
        $this->customerAssignmentDistributionService = $this->buildMockOfInterface(CustomerAssignmentDistributionServiceInterface::class);
        
        $this->task = new AssignCustomerListToSales(
                $this->customerAssignmentRepository, $this->salesRepository, $this->customerRepository,
                $this->customerJourneyRepository, $this->customerAssignmentDistributionService,
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
        $this->customerAssignmentRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturnOnConsecutiveCalls($this->customerAssignmentId, $this->customerAssignmentIdTwo);
        $this->customerAssignmentDistributionService->expects($this->any())
                ->method('getTopPrioritySales')
                ->willReturnOnConsecutiveCalls($this->salesOne, $this->salesTwo);
        $this->customerJourneyRepository->expects($this->any())
                ->method('anInitialCustomerJourney')
                ->willReturn($this->customerJourney);
        $this->task->executeInCompany($this->payload);
    }

    public function test_execute_addCustomerAssignnmentsToRepository()
    {
        $this->salesOne->expects($this->once())
                ->method('receiveCustomerAssignment')
                ->with($this->customerAssignmentId, $this->customer, $this->customerJourney)
                ->willReturn($this->customerAssignment);
        $this->salesTwo->expects($this->once())
                ->method('receiveCustomerAssignment')
                ->with($this->customerAssignmentIdTwo, $this->otherCustomer, $this->customerJourney)
                ->willReturn($this->buildMockOfClass(CustomerAssignment::class));
        $this->customerAssignmentRepository->expects($this->exactly(2))
                ->method('add');
        $this->execute();
    }

    public function test_execute_dispatchEventsInSales()
    {
        $this->dispatcher->expects($this->exactly(2))
                ->method('dispatchEventContainer');
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
        $this->execute();
        $this->markAsSuccess();
    }
}
