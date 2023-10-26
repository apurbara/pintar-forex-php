<?php

namespace Tests\src\Sales\Domain\Task;

use PHPUnit\Framework\MockObject\MockObject;
use Resources\Event\Dispatcher;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Task\Area\AreaRepository;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\Customer\CustomerRepository;
use Tests\TestBase;

class SalesTaskTestBase extends TestBase
{
    protected MockObject $sales;
    protected MockObject $dispatcher;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->dispatcher = $this->buildMockOfClass(Dispatcher::class);
    }
    
    protected MockObject $assignedCustomerRepository;
    protected MockObject $assignedCustomer;
    protected string $assignedCustomerId = 'assignedCustomerId';
    protected function prepareAssignedCustomerDependency()
    {
        $this->assignedCustomerRepository = $this->buildMockOfInterface(AssignedCustomerRepository::class);
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        
        $this->assignedCustomerRepository->expects($this->any())
                ->method('ofId')
                ->with($this->assignedCustomerId)
                ->willReturn($this->assignedCustomer);
    }
    
    protected MockObject $areaRepository;
    protected MockObject $area;
    protected string $areaId = 'areaId';
    protected function prepareAreaDependency()
    {
        $this->areaRepository = $this->buildMockOfInterface(AreaRepository::class);
        $this->area = $this->buildMockOfClass(Area::class);
        
        $this->areaRepository->expects($this->any())
                ->method('ofId')
                ->with($this->areaId)
                ->willReturn($this->area);
    }
    
    protected MockObject $customerRepository;
    protected MockObject $customer;
    protected string $customerId = 'customerId';
    protected function prepareCustomerDependency()
    {
        $this->customerRepository = $this->buildMockOfInterface(CustomerRepository::class);
        $this->customer = $this->buildMockOfClass(Customer::class);
        
//        $this->customerRepository->expects($this->any())
//                ->method('ofId')
//                ->with($this->customerId)
//                ->willReturn($this->customer);
    }
}
