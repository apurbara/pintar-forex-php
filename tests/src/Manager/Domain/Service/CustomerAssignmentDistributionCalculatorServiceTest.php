<?php

namespace Manager\Domain\Service;

use Manager\Domain\Model\Personnel\Manager\Sales;
use Tests\TestBase;

class CustomerAssignmentDistributionCalculatorServiceTest extends TestBase
{
    protected $salesOne, $salesTwo, $salesThree;
    protected $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TestableCustomerAssignmentDistributionCalculatorService();
        
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
    protected function getTopPrioritySalesForCustomerDistribution()
    {
        $this->salesOne->expects($this->any())
                ->method('countActiveAssignmentValue')
                ->willReturn(9);
        $this->salesTwo->expects($this->any())
                ->method('countActiveAssignmentValue')
                ->willReturn(5);
        return $this->service->getTopPrioritySalesForCustomerDistribution();
    }
    public function test_getTopPrioritySalesForCustomerDistribution_scenario_returnSalesWithLowestActiveAssignmentValue()
    {
        $this->assertSame($this->salesTwo, $this->getTopPrioritySalesForCustomerDistribution());
    }
    public function test_getTopPrioritySalesForCustomerDistribution_scenario_returnSalesWithLowestActiveAssignmentValueCaseTwo()
    {
        $this->salesOne->expects($this->any())
                ->method('countActiveAssignmentValue')
                ->willReturn(2);
        $this->assertSame($this->salesOne, $this->getTopPrioritySalesForCustomerDistribution());
    }
}

class TestableCustomerAssignmentDistributionCalculatorService extends CustomerAssignmentDistributionCalculatorService
{
    public array $salesList;
}
