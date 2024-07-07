<?php

namespace Company\Domain\Service;

use Company\Domain\Model\Manager\Sales;
use Tests\TestBase;

class EvenlyCustomerAssignmentDistributionServiceTest extends TestBase
{
    protected $service;
    protected $salesOne, $salesTwo, $salesThree, $salesFour;
    
    protected function setUp(): void
    {
        $this->salesOne = $this->buildMockOfClass(Sales::class);
        $this->salesTwo = $this->buildMockOfClass(Sales::class);
        $this->salesThree = $this->buildMockOfClass(Sales::class);
        $this->salesFour = $this->buildMockOfClass(Sales::class);
        //
        $this->service = new TestableEvenlyCustomerAssignmentDistributionService();
        $this->service->salesList[] = $this->salesOne;
        $this->service->salesList[] = $this->salesTwo;
        $this->service->salesList[] = $this->salesThree;
    }
    
    //
    protected function registerSales()
    {
        $this->service->registerSales($this->salesFour);
    }
    public function test_registerSales_addSalesToList()
    {
        $this->registerSales();
        $this->assertSame($this->salesFour, $this->service->salesList[3]);
    }
    
    //
    protected function getTopPrioritySales()
    {
        return $this->service->getTopPrioritySales();
    }
    public function test_getTopPrioritySales_firstRequest_returnFirstSales()
    {
        $this->assertSame($this->salesOne, $this->getTopPrioritySales());
    }
    public function test_getTopPrioritySales_secondRequest_returnSecondSales()
    {
        $this->assertSame($this->salesOne, $this->getTopPrioritySales());
        $this->assertSame($this->salesTwo, $this->getTopPrioritySales());
    }
    public function test_getTopPrioritySales_requestNPlusOne_returnFirstSales()
    {
        $this->assertSame($this->salesOne, $this->getTopPrioritySales());
        $this->assertSame($this->salesTwo, $this->getTopPrioritySales());
        $this->assertSame($this->salesThree, $this->getTopPrioritySales());
        $this->assertSame($this->salesOne, $this->getTopPrioritySales());
    }
    public function test_getTopPrioritySales_noSalesRegistered()
    {
        $this->service->salesList = [];
        $this->assertNull($this->getTopPrioritySales());
    }
}

class TestableEvenlyCustomerAssignmentDistributionService extends EvenlyCustomerAssignmentDistributionService
{
    public array $salesList;
}
