<?php

namespace Sales\Application\Listener;

use Sales\Application\Listener\AllocateInitialSalesActivityScheduleForMultipleAssignmentListener;
use Sales\Application\Service\Sales\SalesRepository;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;
use Sales\Domain\Task\SalesActivitySchedule\AllocateInitialSalesActivityScheduleForMultipleAssignment;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;
use Tests\TestBase;

class AllocateInitialSalesActivityScheduleForMultipleAssignmentListenerTest extends TestBase
{

    protected $salesRepository, $sales, $salesId = 'salesId';
    protected $salesActivityScheduleRepository, $assignedCustomerRepository, $salesActivityRepository;
    protected $listener;
    //
    protected $event, $assignedCustomerIdOne = 'assignedCustomerIdOne', $assignedCustomerIdTwo = 'assignedCustomerIdTwo';

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
        $this->assignedCustomerRepository = $this->buildMockOfInterface(AssignedCustomerRepository::class);

        $this->listener = new TestableAllocateInitialSalesActivityScheduleForMultipleAssignmentListener($this->salesRepository,
                $this->salesActivityScheduleRepository, $this->assignedCustomerRepository,
                $this->salesActivityRepository);
        //
        $this->event = (new MultipleCustomerAssignmentReceivedBySales($this->salesId))
                ->addAssignedCustomerIdList($this->assignedCustomerIdOne)
                ->addAssignedCustomerIdList($this->assignedCustomerIdTwo);
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
                ->with($this->isInstanceOf(AllocateInitialSalesActivityScheduleForMultipleAssignment::class), [$this->assignedCustomerIdOne, $this->assignedCustomerIdTwo]);
        $this->handle();
    }
    public function test_handle_updateRepository()
    {
        $this->salesRepository->expects($this->once())
                ->method('update');
        $this->handle();
    }
}

class TestableAllocateInitialSalesActivityScheduleForMultipleAssignmentListener extends AllocateInitialSalesActivityScheduleForMultipleAssignmentListener
{

    public AllocateInitialSalesActivityScheduleForMultipleAssignment $task;
}
