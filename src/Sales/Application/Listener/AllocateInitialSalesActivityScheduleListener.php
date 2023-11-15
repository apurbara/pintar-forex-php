<?php

namespace Sales\Application\Listener;

use Resources\Event\EventInterface;
use Resources\Event\ListenerInterface;
use Sales\Application\Service\Sales\ExecuteSalesTask;
use Sales\Application\Service\Sales\SalesRepository;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;
use Sales\Domain\Task\SalesActivitySchedule\AllocateInitialSalesActivitySchedule;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use SharedContext\Domain\Event\CustomerAssignedEvent;

class AllocateInitialSalesActivityScheduleListener implements ListenerInterface
{

    protected ExecuteSalesTask $service;
    protected AllocateInitialSalesActivitySchedule $task;

    public function __construct(
            SalesRepository $salesRepository, SalesActivityScheduleRepository $salesActivityScheduleRepository,
            AssignedCustomerRepository $assignedCustomerRepository, SalesActivityRepository $salesActivityRepository,
            protected string $personnelId, protected string $salesId
    )
    {
        $this->service = new ExecuteSalesTask($salesRepository);
        $this->task = new AllocateInitialSalesActivitySchedule($salesActivityScheduleRepository,
                $assignedCustomerRepository, $salesActivityRepository);
    }

    public function handle(EventInterface $event): void
    {
        $this->execute($event);
    }

    protected function execute(CustomerAssignedEvent $event): void
    {
        $this->service->execute(
                $this->personnelId, $this->salesId, $this->task,
                $event->assignedCustomerId);
    }
}
