<?php

namespace User\Application\Service\Guest;

use Tests\TestBase;
use User\Domain\Model\Sales;

class SalesLoginServiceTest extends TestBase
{
    protected $salesRepository;
    protected $sales, $salesId = 'salesId';
    protected $service;
    //
    protected $email = 'sales@email.org', $password = 'password123';
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->service = new SalesLoginService($this->salesRepository);
        
        $this->sales = $this->buildMockOfClass(Sales::class);
    }
    
    //
    protected function execute()
    {
        $this->salesRepository->expects($this->any())
                ->method('activeSalesByEmail')
                ->with($this->email)
                ->willReturn($this->sales);
        return $this->service->execute($this->email, $this->password);
    }
    public function test_execute_returnSalesLoginResult()
    {
        $this->sales->expects($this->once())
                ->method('login')
                ->with($this->password)
                ->willReturn($this->salesId);
        $this->assertSame($this->salesId, $this->execute());
    }
}
