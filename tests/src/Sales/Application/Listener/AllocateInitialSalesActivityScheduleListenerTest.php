<?php

namespace Sales\Application\Listener;

use Sales\Application\Service\Sales\ExecuteSalesTask;
use Sales\Application\Service\Sales\SalesRepository;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;
use Sales\Domain\Task\SalesActivitySchedule\AllocateInitialSalesActivitySchedule;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use Tests\TestBase;

class AllocateInitialSalesActivityScheduleListenerTest extends TestBase
{

    protected $salesRepository;
    protected $salesActivityScheduleRepository;
    protected $assignedCustomerRepository;
    protected $salesActivityRepository;
    protected $personnelId = 'personnelId', $salesId = 'salesId';
    protected $listener, $service, $task;
    //
    protected $event, $assignedCustomerId = 'assignedCustomerId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->salesRepository = $this->buildMockOfInterface(SalesRepository::class);
        $this->salesActivityScheduleRepository = $this->buildMockOfInterface(SalesActivityScheduleRepository::class);
        $this->assignedCustomerRepository = $this->buildMockOfInterface(AssignedCustomerRepository::class);
        $this->salesActivityRepository = $this->buildMockOfInterface(SalesActivityRepository::class);

        $this->listener = new TestableAllocateInitialSalesActivityScheduleListener($this->salesRepository,
                $this->salesActivityScheduleRepository, $this->assignedCustomerRepository,
                $this->salesActivityRepository, $this->personnelId, $this->salesId);

        $this->service = $this->buildMockOfClass(ExecuteSalesTask::class);
        $this->listener->service = $this->service;

        $this->task = $this->buildMockOfClass(AllocateInitialSalesActivitySchedule::class);
        $this->listener->task = $this->task;
        //
        $this->event = new CustomerAssignedEvent($this->assignedCustomerId);
    }

    //
    protected function construct()
    {
        return new TestableAllocateInitialSalesActivityScheduleListener($this->salesRepository,
                $this->salesActivityScheduleRepository, $this->assignedCustomerRepository,
                $this->salesActivityRepository, $this->personnelId, $this->salesId);
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
                ->with($this->personnelId, $this->salesId, $this->task, $this->assignedCustomerId);
        $this->handle();
    }
}

class TestableAllocateInitialSalesActivityScheduleListener extends AllocateInitialSalesActivityScheduleListener
{

    public ExecuteSalesTask $service;
    public AllocateInitialSalesActivitySchedule $task;
}
