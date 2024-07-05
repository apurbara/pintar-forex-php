<?php

namespace Sales\Application\Listener;

use Sales\Application\Service\Sales\SalesRepository;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\SalesActivitySchedule\AllocateInitialSalesActivityScheduleForMultipleAssignment;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;
use Tests\TestBase;

class AllocateInitialSalesActivityScheduleForMultipleAssignmentListenerTest extends TestBase
{

    protected $salesRepository, $sales, $salesId = 'salesId';
    protected $salesActivityScheduleRepository, $customerAssignmentRepository, $salesActivityRepository;
    protected $listener;
    //
    protected $event, $customerAssignmentIdOne = 'customerAssignmentIdOne', $customerAssignmentIdTwo = 'customerAssignmentIdTwo';

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

        $this->listener = new TestableAllocateInitialSalesActivityScheduleForMultipleAssignmentListener($this->salesRepository,
                $this->salesActivityScheduleRepository, $this->customerAssignmentRepository,
                $this->salesActivityRepository);
        //
        $this->event = (new MultipleCustomerAssignmentReceivedBySales($this->salesId))
                ->addCustomerAssignmentId($this->customerAssignmentIdOne)
                ->addCustomerAssignmentId($this->customerAssignmentIdTwo);
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
                ->with($this->isInstanceOf(AllocateInitialSalesActivityScheduleForMultipleAssignment::class), [$this->customerAssignmentIdOne, $this->customerAssignmentIdTwo]);
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
