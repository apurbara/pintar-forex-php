<?php

namespace Company\Domain\Service;

use Company\Domain\Model\Sales;
use Tests\TestBase;

class LoadBalanceCustomerAssignmentDistributionServiceTest extends TestBase
{
    protected $salesOne, $salesTwo, $salesThree;
    protected $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TestableLoadBalanceCustomerAssignmentDistributionService();
        
        $this->salesOne = $this->buildMockOfClass(Sales::class);
        $this->salesTwo = $this->buildMockOfClass(Sales::class);
        $this->salesThree = $this->buildMockOfClass(Sales::class);
        $this->service->salesList = [$this->salesOne, $this->salesTwo];
    }
    
    //
    protected function registerSales()
    {
        $this->service->registerSales($this->salesThree);
    }
    public function test_registerSales_addSalesToList()
    {
        $this->registerSales();
        $this->assertSame([$this->salesOne, $this->salesTwo, $this->salesThree], $this->service->salesList);
    }
    
    //
    protected function getTopPrioritySales()
    {
        $this->salesOne->expects($this->any())
                ->method('calculateActiveCustomerAssignmentsCount')
                ->willReturn(9);
        $this->salesTwo->expects($this->any())
                ->method('calculateActiveCustomerAssignmentsCount')
                ->willReturn(5);
        return $this->service->getTopPrioritySales();
    }
    public function test_getTopPrioritySales_scenario_returnSalesWithLowestActiveAssignmentValue()
    {
        $this->assertSame($this->salesTwo, $this->getTopPrioritySales());
    }
    public function test_getTopPrioritySales_scenario_returnSalesWithLowestActiveAssignmentValueCaseTwo()
    {
        $this->salesOne->expects($this->any())
                ->method('calculateActiveCustomerAssignmentsCount')
                ->willReturn(2);
        $this->assertSame($this->salesOne, $this->getTopPrioritySales());
    }
}

class TestableLoadBalanceCustomerAssignmentDistributionService extends LoadBalanceCustomerAssignmentDistributionService
{
    public array $salesList;
}
