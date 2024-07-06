<?php

namespace Sales\Application\Listener;

use Resources\Event\EventInterface;
use Resources\Event\ListenerInterface;
use Sales\Domain\Task\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use Sales\Domain\Task\SalesActivitySchedule\AllocateInitialSalesActivitySchedule;
use Sales\Domain\Task\SalesActivitySchedule\SalesActivityScheduleRepository;
use Sales\Domain\Task\SalesRepository;
use Shared\Domain\Event\CustomerAssignedEvent;

class AllocateInitialSalesActivityScheduleListener implements ListenerInterface
{

    public function __construct(
            protected SalesRepository $salesRepository,
            protected SalesActivityScheduleRepository $salesActivityScheduleRepository,
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected SalesActivityRepository $salesActivityRepository, protected string $salesId
    )
    {
        
    }

    public function handle(EventInterface $event): void
    {
        $this->execute($event);
    }

    protected function execute(CustomerAssignedEvent $event): void
    {
        $task = new AllocateInitialSalesActivitySchedule($this->salesActivityScheduleRepository,
                $this->customerAssignmentRepository, $this->salesActivityRepository);
        $this->salesRepository->ofId($this->salesId)
                ->executeTask($task, $event->customerAssignmentId);
        $this->salesRepository->update();
    }
}
