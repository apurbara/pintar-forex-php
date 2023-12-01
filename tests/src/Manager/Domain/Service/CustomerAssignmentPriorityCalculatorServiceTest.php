<?php

namespace Manager\Domain\Service;

use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Tests\TestBase;

class CustomerAssignmentPriorityCalculatorServiceTest extends TestBase
{
    protected $service;
    //
    protected $salesOne;
    protected $salesTwo;
    
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TestableCustomerAssignmentPriorityCalculatorService();
        //
        $this->salesOne = $this->buildMockOfClass(Sales::class);
        $this->salesTwo = $this->buildMockOfClass(Sales::class);
        
        $this->customer = $this->buildMockOfClass(Customer::class);
    }
    
    //
    protected function registerSales()
    {
        $this->service->registerSales($this->salesOne);
    }
    public function test_registerSales_appendSalesToList()
    {
        $this->registerSales();
        $this->assertEquals($this->salesOne, $this->service->salesList[0]);
    }
    
    //
    protected function getTopPrioritySalesForCustomerAssignment()
    {
        $this->salesOne->expects($this->any())
                ->method('countAssignmentPriorityWithCustomer')
                ->with($this->customer)
                ->willReturn(INF);
        $this->salesTwo->expects($this->any())
                ->method('countAssignmentPriorityWithCustomer')
                ->with($this->customer)
                ->willReturn(INF);
        
        $this->service->registerSales($this->salesOne);
        $this->service->registerSales($this->salesTwo);
        return $this->service->getTopPrioritySalesForCustomerAssignment($this->customer);
    }
    public function test_getTopPrioritySalesForCustomerAssignment_hasSalesWithNonInfinitePriority_returnCorrespondingSales()
    {
        $this->salesTwo->expects($this->any())
                ->method('countAssignmentPriorityWithCustomer')
                ->with($this->customer)
                ->willReturn(99);
        $this->assertSame($this->salesTwo, $this->getTopPrioritySalesForCustomerAssignment());
    }
    public function test_getTopPrioritySalesForCustomerAssignment_conatinSalesWithBetterPriority_returnBetterPrioritySales()
    {
        $this->salesOne->expects($this->any())
                ->method('countAssignmentPriorityWithCustomer')
                ->with($this->customer)
                ->willReturn(23);
        $this->salesTwo->expects($this->any())
                ->method('countAssignmentPriorityWithCustomer')
                ->with($this->customer)
                ->willReturn(99);
        $this->assertSame($this->salesOne, $this->getTopPrioritySalesForCustomerAssignment());
    }
    
}

class TestableCustomerAssignmentPriorityCalculatorService extends CustomerAssignmentPriorityCalculatorService
{
    public array $salesList;
}
