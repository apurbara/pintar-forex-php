<?php

namespace Manager\Application\Listener;

use Manager\Application\Service\Manager\ExecuteManagerTask;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomerData;
use Manager\Domain\Task\AssignedCustomer\AssignCustomerToTopPriorityFreelanceSales;
use Resources\Event\EventInterface;
use Resources\Event\ListenerInterface;
use SharedContext\Domain\Event\InHouseSalesCustomerAssignmentRecycledEvent;

readonly class DistributeRecycledCustomerFromInHouseSalesListener implements ListenerInterface
{

    public function __construct(protected ExecuteManagerTask $service,
            protected AssignCustomerToTopPriorityFreelanceSales $task, protected string $personnelId,
            protected string $managerId)
    {
        
    }

    public function handle(EventInterface $event): void
    {
        $this->execute($event);
    }

    protected function execute(InHouseSalesCustomerAssignmentRecycledEvent $event): void
    {
        $payload = (new AssignedCustomerData())
                ->setCustomerId($event->customerId);
        $this->service->excute($this->personnelId, $this->managerId, $this->task, $payload);
    }
}
