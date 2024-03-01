<?php

namespace Manager\Domain\Task\AssignedCustomer;

use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomerData;
use Manager\Domain\Service\CustomerAssignmentPriorityCalculatorService;
use PHPUnit\Framework\MockObject\MockObject;
use Resources\Event\Dispatcher;
use Tests\src\Manager\Domain\Task\ManagerTaskTestBase;

class AssignCustomerToTopPriorityFreelanceSalesTest extends ManagerTaskTestBase
{
    protected $task, $dispatcher, $assignmentPriorityCalculator;
    protected $payload;
    //
    protected MockObject $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAssignedCustomerDependency();
        $this->prepareCustomerDependency();
        $this->prepareCustomerJourneyDependency();
        
        $this->assignmentPriorityCalculator = $this->buildMockOfClass(CustomerAssignmentPriorityCalculatorService::class);
        $this->dispatcher = $this->buildMockOfClass(Dispatcher::class);
        
        //
        $this->task = new AssignCustomerToTopPriorityFreelanceSales($this->assignedCustomerRepository, $this->customerRepository, $this->customerJourneyRepository, $this->assignmentPriorityCalculator, $this->dispatcher);
        
        //
        $this->payload = (new AssignedCustomerData())
                ->setCustomerId($this->customerId);
        
        $this->sales = $this->buildMockOfClass(Sales::class);
    }
    
    //
    protected function execute()
    {
        $this->assignedCustomerRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->assignedCustomerId);
        
        $this->customerJourneyRepository->expects($this->any())
                ->method('anInitialCustomerJourney')
                ->willReturn($this->customerJourney);
        
        $this->assignmentPriorityCalculator->expects($this->any())
                ->method('getTopPrioritySalesForCustomerAssignment')
                ->with($this->customer)
                ->willReturn($this->sales);
        
        $this->sales->expects($this->any())
                ->method('receiveCustomerAssignment')
                ->with($this->assignedCustomerId, $this->customer, $this->customerJourney)
                ->willReturn($this->assignedCustomer);
        
        $this->task->executeByManager($this->manager, $this->payload);
    }
    public function test_execute_addAssignentToTopPrioritySalesFromCalculatorToRepository()
    {
        $this->assignedCustomerRepository->expects($this->once())
                ->method('add')
                ->with($this->assignedCustomer);
        $this->execute();
    }
    public function test_execute_managerRegisterActiveFreelanceSalesToAssignmentPriorityCalculator()
    {
        $this->manager->expects($this->once())
                ->method('registerActiveFreelanceSales')
                ->with($this->assignmentPriorityCalculator);
        $this->execute();
    }
    public function test_execute_dispatchAssignedCustomer()
    {
        $this->dispatcher->expects($this->once())
                ->method('dispatchEventContainer')
                ->with($this->assignedCustomer);
        $this->execute();
    }
    
}
