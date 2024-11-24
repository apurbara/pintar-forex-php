<?php

namespace Company\Domain\Task\CustomerAssignment;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Task\Customer\CustomerRepository;
use Company\Domain\Task\Sales\SalesRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class AssignFactFindingActivityOfCustomerListToSalesTest extends TaskInCompanyTestBase
{

    protected MockObject $salesRepository, $customerRepository, $customerAssignmentDistributionService;
    protected MockObject $salesOne, $salesTwo, $customer, $otherCustomer;
    protected string $salesOneId = 'salesOneId', $salesTwoId = 'salesTwoId', $customerId = 'customerId', $otherCustomerId = 'otherCustomerId';
    protected $dispatcher;
    protected $task;
    protected $payload, $secondCustomerId;
    protected $secondCustomer;
    protected $factFindingAssignmentIdTwo = 'factFindingAssignmentIdTwo';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFactFindingAssignmentDependency();
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
        
        $this->task = new AssignFactFindingActivityOfCustomerListToSales(
                $this->factFindingAssignmentRepository, $this->salesRepository, $this->customerRepository,
                $this->customerAssignmentDistributionService);
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
        $this->factFindingAssignmentRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturnOnConsecutiveCalls($this->factFindingAssignmentId, $this->factFindingAssignmentIdTwo);
        $this->customerAssignmentDistributionService->expects($this->any())
                ->method('getTopPrioritySales')
                ->willReturnOnConsecutiveCalls($this->salesOne, $this->salesTwo);
        $this->task->executeInCompany($this->payload);
    }

    public function test_execute_addCustomerAssignnmentsToRepository()
    {
        $this->factFindingAssignmentRepository->expects($this->exactly(2))
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
        $this->factFindingAssignmentRepository->expects($this->never())
                ->method('add');
        $this->execute();
        $this->markAsSuccess();
    }
}
