<?php

namespace Sales\Application\Listener;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use Sales\Domain\Task\SalesActivitySchedule\AllocateInitialSalesActivitySchedule;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\SalesRepository;
use Shared\Domain\Event\CustomerAssignedEvent;
use Tests\TestBase;

class AllocateInitialSalesActivityScheduleListenerTest extends TestBase
{

    protected $salesRepository, $sales, $salesId = 'salesId';
    protected $salesActivityScheduleRepository, $customerAssignmentRepository, $salesActivityRepository;
    protected $listener;
    //
    protected $event, $customerAssignmentId = 'customerAssignmentId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->sales = $this->buildMockOfClass(Sales::class);
        $this->salesRepository->expects($this->any())
                ->method('ofId')
                ->with($this->salesId)
                ->willReturn($this->sales);
        
        $this->salesActivityScheduleRepository = $this->buildMockOfInterface(SalesActivityScheduleRepository::class);
        $this->salesActivityRepository = $this->buildMockOfInterface(SalesActivityRepository::class);
        $this->customerAssignmentRepository = $this->buildMockOfInterface(CustomerAssignmentRepository::class);

        $this->listener = new AllocateInitialSalesActivityScheduleListener($this->salesRepository,
                $this->salesActivityScheduleRepository, $this->customerAssignmentRepository,
                $this->salesActivityRepository, $this->salesId);
        
        $this->event = new CustomerAssignedEvent($this->customerAssignmentId);
    }

    //
    protected function handle()
    {
        $this->listener->handle($this->event);
    }
    public function test_handle_salesExecuteTask()
    {
        $this->sales->expects($this->once())
                ->method('executeTask')
                ->with($this->isInstanceOf(AllocateInitialSalesActivitySchedule::class), $this->customerAssignmentId);
        $this->handle();
    }
    public function test_handle_updateRepository()
    {
        $this->salesRepository->expects($this->once())
                ->method('update');
        $this->handle();
    }
}
