<?php

namespace Company\Application\EventHandler;

use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Service\SalesFinderService;
use Sales\Domain\Event\CustomerVerified;
use Tests\TestBase;

class CustomerVerifiedEventHandlerTest extends TestBase
{

    protected $strikingAssignmentRepository, $strikingAssignment, $strikingAssignmentId = 'strikingAssignmentId';
    protected $factFindingAssignmentRepository, $factFindingAssignment, $factFindingAssignmentId = 'factFindingAssignmentId';
    protected $customerJourneyRepository, $customerJourney, $customerJourneyId = 'customerJourneyId';
    protected $salesFinderService;
    protected $eventHandler;
    protected $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strikingAssignmentRepository = $this->buildMockOfInterface(StrikingAssignmentRepository::class);
        $this->strikingAssignment = $this->buildMockOfClass(StrikingAssignment::class);

        $this->factFindingAssignmentRepository = $this->buildMockOfInterface(FactFindingAssignmentRepository::class);
        $this->factFindingAssignment = $this->buildMockOfClass(FactFindingAssignment::class);

        $this->customerJourneyRepository = $this->buildMockOfInterface(CustomerJourneyRepository::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        
        $this->salesFinderService = $this->buildMockOfClass(SalesFinderService::class);

        $this->eventHandler = new CustomerVerifiedEventHandler($this->strikingAssignmentRepository,
                $this->factFindingAssignmentRepository, $this->salesFinderService, $this->customerJourneyRepository);

        $this->event = new CustomerVerified($this->factFindingAssignmentId);
    }

    //
    protected function handle()
    {
        $this->strikingAssignmentRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->strikingAssignmentId);

        $this->factFindingAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->factFindingAssignmentId)
                ->willReturn($this->factFindingAssignment);

        $this->customerJourneyRepository->expects($this->any())
                ->method('anInitialCustomerJourney')
                ->willReturn($this->customerJourney);

        $this->eventHandler->handle($this->event);
    }

    public function test_handle_scenario_addStrikingHandedOverByFactFindingAssignmentToRepository()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('handoverCustomerToStriker')
                ->with($this->salesFinderService, $this->strikingAssignmentId, $this->customerJourney)
                ->willReturn($this->strikingAssignment);
        $this->strikingAssignmentRepository->expects($this->once())
                ->method('add')
                ->with($this->strikingAssignment);
        $this->handle();
    }

    public function test_handle_scenario_noHandoverResult()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('handoverCustomerToStriker')
                ->with($this->salesFinderService, $this->strikingAssignmentId, $this->customerJourney);
        $this->strikingAssignmentRepository->expects($this->never())
                ->method('add');
        $this->handle();
    }
    public function test_handle_updateRepository()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('handoverCustomerToStriker')
                ->with($this->salesFinderService, $this->strikingAssignmentId, $this->customerJourney)
                ->willReturn($this->strikingAssignment);
        $this->strikingAssignmentRepository->expects($this->once())
                ->method('update');
        $this->handle();
    }
}
