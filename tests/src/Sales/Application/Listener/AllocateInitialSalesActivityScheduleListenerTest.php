<?php

namespace Sales\Application\Listener;

use Sales\Application\Service\Sales\ExecuteSalesTask;
use Sales\Application\Service\Sales\SalesRepository;
use Sales\Domain\Task\BySales\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\BySales\SalesActivitySchedule\AllocateInitialSalesActivitySchedule;
use Sales\Domain\Task\BySales\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use Tests\TestBase;

class AllocateInitialSalesActivityScheduleListenerTest extends TestBase
{

    protected $salesRepository;
    protected $salesActivityScheduleRepository;
    protected $customerAssignmentRepository;
    protected $salesActivityRepository;
    protected $salesId = 'salesId';
    protected $listener, $service, $task;
    //
    protected $event, $customerAssignmentId = 'customerAssignmentId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->salesActivityScheduleRepository = $this->buildMockOfInterface(SalesActivityScheduleRepository::class);
        $this->customerAssignmentRepository = $this->buildMockOfInterface(CustomerAssignmentRepository::class);
        $this->salesActivityRepository = $this->buildMockOfInterface(SalesActivityRepository::class);

        $this->listener = new TestableAllocateInitialSalesActivityScheduleListener($this->salesRepository,
                $this->salesActivityScheduleRepository, $this->customerAssignmentRepository,
                $this->salesActivityRepository, $this->salesId);

        $this->service = $this->buildMockOfClass(ExecuteSalesTask::class);
        $this->listener->service = $this->service;

        $this->task = $this->buildMockOfClass(AllocateInitialSalesActivitySchedule::class);
        $this->listener->task = $this->task;
        //
        $this->event = new CustomerAssignedEvent($this->customerAssignmentId);
    }

    //
    protected function construct()
    {
        return new TestableAllocateInitialSalesActivityScheduleListener($this->salesRepository,
                $this->salesActivityScheduleRepository, $this->customerAssignmentRepository,
                $this->salesActivityRepository, $this->salesId);
    }
    public function test_construct_setProperties()
    {
        $listener = $this->construct();
        $this->assertInstanceOf(ExecuteSalesTask::class, $listener->service);
        $this->assertInstanceOf(AllocateInitialSalesActivitySchedule::class, $listener->task);
    }

    //
    protected function handle()
    {
        $this->listener->handle($this->event);
    }

    public function test_handle_serviceExecuteTask()
    {
        $this->service->expects($this->once())
                ->method('execute')
                ->with($this->salesId, $this->task, $this->customerAssignmentId);
        $this->handle();
    }
}

class TestableAllocateInitialSalesActivityScheduleListener extends AllocateInitialSalesActivityScheduleListener
{

    public ExecuteSalesTask $service;
    public AllocateInitialSalesActivitySchedule $task;
}
