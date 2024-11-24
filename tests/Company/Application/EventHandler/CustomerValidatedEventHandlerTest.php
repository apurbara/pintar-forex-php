<?php

namespace Company\Application\EventHandler;

use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use Company\Domain\Service\SalesFinderService;
use Sales\Domain\Event\CustomerValidated;
use Tests\TestBase;

class CustomerValidatedEventHandlerTest extends TestBase
{
    protected $factFindingAssignmentRepository, $factFindingAssignment, $factFindingAssignmentId = 'factFindingAssignmentId';
    protected $greetingAssignmentRepository, $greetingAssignment, $greetingAssignmentId = 'greetingAssignmentId';
    protected $salesFinderService;
    protected $eventHandler;
    protected $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factFindingAssignmentRepository = $this->buildMockOfInterface(FactFindingAssignmentRepository::class);
        $this->factFindingAssignment = $this->buildMockOfClass(FactFindingAssignment::class);
        
        $this->greetingAssignmentRepository = $this->buildMockOfInterface(GreetingAssignmentRepository::class);
        $this->greetingAssignment = $this->buildMockOfClass(GreetingAssignment::class);
        $this->salesFinderService = $this->buildMockOfClass(SalesFinderService::class);
        
        $this->eventHandler = new CustomerValidatedEventHandler($this->factFindingAssignmentRepository, $this->greetingAssignmentRepository, $this->salesFinderService);
        
        $this->event = new CustomerValidated($this->greetingAssignmentId);
    }
    
    //
    protected function handle()
    {
        $this->factFindingAssignmentRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->factFindingAssignmentId);
        
        $this->greetingAssignmentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->greetingAssignmentId)
                ->willReturn($this->greetingAssignment);
        
        $this->eventHandler->handle($this->event);
    }
    public function test_handle_scenario_addFactFindingHandedOverByGreetingAssignmentToRepository()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('handoverCustomerToFactFinder')
                ->with($this->salesFinderService, $this->factFindingAssignmentId)
                ->willReturn($this->factFindingAssignment);
        $this->factFindingAssignmentRepository->expects($this->once())
                ->method('add')
                ->with($this->factFindingAssignment);
        $this->handle();
    }
    public function test_handle_scenario_noHandoverResult()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('handoverCustomerToFactFinder')
                ->with($this->salesFinderService, $this->factFindingAssignmentId);
        $this->factFindingAssignmentRepository->expects($this->never())
                ->method('add');
        $this->handle();
    }
    public function test_handle_updateRepository()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('handoverCustomerToFactFinder')
                ->with($this->salesFinderService, $this->factFindingAssignmentId)
                ->willReturn($this->factFindingAssignment);
        $this->factFindingAssignmentRepository->expects($this->once())
                ->method('update');
        $this->handle();
    }
}
