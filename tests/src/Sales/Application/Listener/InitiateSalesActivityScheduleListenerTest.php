<?php

namespace Sales\Application\Listener;

use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use Tests\TestBase;

class InitiateSalesActivityScheduleListenerTest extends TestBase
{
    protected $assignedCustomerRepository, $assignedCustomer, $assignedCustomerId = 'assignedCustomerId';
    protected $salesActivityRepository, $salesActivity;
    protected $listener;
    protected $event;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->assignedCustomerRepository = $this->buildMockOfInterface(AssignedCustomerRepository::class);
        $this->assignedCustomer = $this->buildMockOfClass(AssignedCustomer::class);
        
        $this->salesActivityRepository = $this->buildMockOfInterface(SalesActivityRepository::class);
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        
        $this->listener = new InitiateSalesActivityScheduleListener($this->assignedCustomerRepository, $this->salesActivityRepository);
        
        $this->event = new CustomerAssignedEvent($this->assignedCustomerId);
    }
    
    //
    protected function handle()
    {
        $this->assignedCustomerRepository->expects($this->any())
                ->method('ofId')
                ->with($this->assignedCustomerId)
                ->willReturn($this->assignedCustomer);
        $this->salesActivityRepository->expects($this->any())
                ->method('anInitialSalesActivity')
                ->willReturn($this->salesActivity);
        $this->listener->handle($this->event);
    }
    public function test_handle_initiateSalesActivityScheduleInAssignment()
    {
        $this->assignedCustomer->expects($this->once())
                ->method('initiateSalesActivitySchedule')
                ->with($this->salesActivity, $this->anything());
        $this->handle();
    }
    public function test_handle_updateRepository()
    {
        $this->assignedCustomerRepository->expects($this->once())
                ->method('update');
        $this->handle();
    }
}
