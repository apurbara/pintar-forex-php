<?php

namespace Sales\Application\Listener;

use Resources\Event\EventInterface;
use Resources\Event\ListenerInterface;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use Sales\Domain\Task\SalesActivitySchedule\AllocateInitialSalesActivityScheduleForMultipleAssignment;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\SalesRepository;
use Shared\Domain\Event\MultipleCustomerAssignmentReceivedBySales;

class AllocateInitialSalesActivityScheduleForMultipleAssignmentListener implements ListenerInterface
{

    public function __construct(
            protected SalesRepository $salesRepository,
            protected SalesActivityScheduleRepository $salesActivityScheduleRepository,
            protected CustomerAssignmentRepository $customerAssignmentRepository,
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
                $this->salesActivityScheduleRepository, $this->customerAssignmentRepository,
                $this->salesActivityRepository);
        $this->salesRepository->ofId($event->salesId)
                ->executeTask($task, $event->getCustomerAssignmentIdList());
        $this->salesRepository->update();
    }
}
