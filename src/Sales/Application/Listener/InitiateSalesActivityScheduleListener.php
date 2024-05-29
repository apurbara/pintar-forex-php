<?php

namespace Sales\Application\Listener;

use Resources\Event\EventInterface;
use Resources\Event\ListenerInterface;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\BySales\CustomerAssignment\CustomerAssignmentRepository;
use Sales\Domain\Task\Dependency\SalesActivityRepository;
use SharedContext\Domain\Event\CustomerAssignedEvent;

readonly class InitiateSalesActivityScheduleListener implements ListenerInterface
{

    public function __construct(
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected SalesActivityRepository $salesActivityRepository)
    {
        
    }

    public function handle(EventInterface $event): void
    {
        $this->execute($event);
    }

    private function execute(CustomerAssignedEvent $event): void
    {
        $initialSalesActivity = $this->salesActivityRepository->anInitialSalesActivity();
        $service = new SalesActivitySchedulerService();
        $this->customerAssignmentRepository->ofId($event->customerAssignmentId)
                ->initiateSalesActivitySchedule($initialSalesActivity, $service);
        $this->customerAssignmentRepository->update();
    }
}
