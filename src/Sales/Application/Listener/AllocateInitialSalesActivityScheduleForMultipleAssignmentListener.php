<?php

namespace Sales\Application\Listener;

use Resources\Event\EventInterface;
use Resources\Event\ListenerInterface;
use Sales\Application\Service\Sales\SalesRepository;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;
use Sales\Domain\Task\SalesActivitySchedule\AllocateInitialSalesActivityScheduleForMultipleAssignment;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;

class AllocateInitialSalesActivityScheduleForMultipleAssignmentListener implements ListenerInterface
{

    public function __construct(
            protected SalesRepository $salesRepository,
            protected SalesActivityScheduleRepository $salesActivityScheduleRepository,
            protected AssignedCustomerRepository $assignedCustomerRepository,
            protected SalesActivityRepository $salesActivityRepository
    )
    {
        
    }

    public function handle(EventInterface $event): void
    {
        $this->execute($event);
    }

    //
    private function execute(MultipleCustomerAssignmentReceivedBySales $event): void
    {
        $task = new AllocateInitialSalesActivityScheduleForMultipleAssignment(
                $this->salesActivityScheduleRepository, $this->assignedCustomerRepository,
                $this->salesActivityRepository);
        $this->salesRepository->ofId($event->salesId)
                ->executeTask($task, $event->getAssignedCustomerIdList());
        $this->salesRepository->update();
    }
}
