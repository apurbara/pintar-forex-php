<?php

namespace Company\Application\EventHandler;

use Company\Domain\Service\SalesFinderService;
use Resources\Event\EventInterface;
use Resources\Event\ListenerInterface;
use Sales\Domain\Event\CustomerValidated;

class CustomerValidatedEventHandler implements ListenerInterface
{

    public function __construct(
            protected FactFindingAssignmentRepository $factFindingAssignmentRepository,
            protected GreetingAssignmentRepository $greetingAssignmentRepository,
            protected SalesFinderService $salesFinderService)
    {
        
    }

    public function handle(EventInterface $event): void
    {
        $this->execute($event);
    }

    private function execute(CustomerValidated $event): void
    {
        $greetingAssignment = $this->greetingAssignmentRepository->ofId($event->greetingAssignmentId);
        $factFindingAssignment = $greetingAssignment->handoverCustomerToFactFinder(
                $this->salesFinderService, $this->factFindingAssignmentRepository->nextIdentity());
        if (isset($factFindingAssignment)) {
            $this->factFindingAssignmentRepository->add($factFindingAssignment);
            $this->factFindingAssignmentRepository->update();
        }
    }
}
