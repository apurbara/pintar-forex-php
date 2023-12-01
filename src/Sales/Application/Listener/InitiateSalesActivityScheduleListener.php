<?php

namespace Sales\Application\Listener;

use Resources\Event\EventInterface;
use Resources\Event\ListenerInterface;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\AssignedCustomer\AssignedCustomerRepository;
use Sales\Domain\Task\SalesActivity\SalesActivityRepository;
use SharedContext\Domain\Event\CustomerAssignedEvent;

readonly class InitiateSalesActivityScheduleListener implements ListenerInterface
{

    public function __construct(
            protected AssignedCustomerRepository $assignedCustomerRepository,
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
        $this->assignedCustomerRepository->ofId($event->assignedCustomerId)
                ->initiateSalesActivitySchedule($initialSalesActivity, $service);
        $this->assignedCustomerRepository->update();
    }
}
