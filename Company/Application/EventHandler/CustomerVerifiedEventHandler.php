<?php

namespace Company\Application\EventHandler;

use Company\Domain\Service\SalesFinderService;
use Resources\Event\EventInterface;
use Resources\Event\ListenerInterface;
use Sales\Domain\Event\CustomerVerified;

class CustomerVerifiedEventHandler implements ListenerInterface
{

    public function __construct(
            protected StrikingAssignmentRepository $strikingAssignmentRepository,
            protected FactFindingAssignmentRepository $factFindingAssignmentRepository,
            protected SalesFinderService $salesFinderService,
            protected CustomerJourneyRepository $customerJourneyRepository)
    {
        
    }

    public function handle(EventInterface $event): void
    {
        $this->execute($event);
    }

    private function execute(CustomerVerified $event): void
    {
        $factFindingAssignment = $this->factFindingAssignmentRepository->ofId($event->factFindingAssignmentId);
        $strikingAssignment = $factFindingAssignment->handoverCustomerToStriker(
                $this->salesFinderService, $this->strikingAssignmentRepository->nextIdentity(),
                $this->customerJourneyRepository->anInitialCustomerJourney());
        if (isset($strikingAssignment)) {
            $this->strikingAssignmentRepository->add($strikingAssignment);
            $this->strikingAssignmentRepository->update();
        }
    }
}
