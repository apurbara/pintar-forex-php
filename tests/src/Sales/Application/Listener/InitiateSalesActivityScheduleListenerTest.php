<?php

namespace Sales\Application\Listener;

use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Task\BySales\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use Tests\TestBase;

class InitiateSalesActivityScheduleListenerTest extends TestBase
{
    protected $customerAssignmentRepository, $customerAssignment, $customerAssignmentId = 'customerAssignmentId';
    protected $salesActivityRepository, $salesActivity;
    protected $listener;
    protected $event;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAssignmentRepository = $this->buildMockOfInterface(CustomerAssignmentRepository::class);
        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
        
        $this->salesActivityRepository = $this->buildMockOfInterface(SalesActivityRepository::class);
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
        
        $this->listener = new InitiateSalesActivityScheduleListener($this->customerAssignmentRepository, $this->salesActivityRepository);
        
        $this->event = new CustomerAssignedEvent($this->customerAssignmentId);
    }
    
    //
    protected function handle()
    {
        $this->customerAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->customerAssignmentId)
                ->willReturn($this->customerAssignment);
        $this->salesActivityRepository->expects($this->any())
                ->method('anInitialSalesActivity')
                ->willReturn($this->salesActivity);
        $this->listener->handle($this->event);
    }
    public function test_handle_initiateSalesActivityScheduleInAssignment()
    {
        $this->customerAssignment->expects($this->once())
                ->method('initiateSalesActivitySchedule')
                ->with($this->salesActivity, $this->anything());
        $this->handle();
    }
    public function test_handle_updateRepository()
    {
        $this->customerAssignmentRepository->expects($this->once())
                ->method('update');
        $this->handle();
    }
}
