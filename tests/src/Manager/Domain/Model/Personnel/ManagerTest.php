<?php

namespace Manager\Domain\Model\Personnel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Manager\Domain\Model\Personnel;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Service\CustomerAssignmentPriorityCalculatorService;
use Manager\Domain\Task\ManagerTask;
use SharedContext\Domain\Enum\SalesType;
use Tests\TestBase;

class ManagerTest extends TestBase
{
    protected $manager;
    protected $salesOne;
    protected $salesTwo;
    //
    protected $task, $payload = 'task payload';
    protected $assignmentPriorityCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new TestableManager();
        $this->manager->salesCollection = new ArrayCollection();
        
        $this->salesOne = $this->buildMockOfClass(Sales::class);
        $this->salesTwo = $this->buildMockOfClass(Sales::class);
        $this->manager->salesCollection->add($this->salesOne);
        $this->manager->salesCollection->add($this->salesTwo);
        //
        $this->task = $this->buildMockOfInterface(ManagerTask::class);
        $this->assignmentPriorityCalculator = $this->buildMockOfClass(CustomerAssignmentPriorityCalculatorService::class);
    }
    
    //
    protected function executeTask()
    {
        $this->manager->executeTask($this->task, $this->payload);
    }
    public function test_executeTask_executeTask()
    {
        $this->task->expects($this->once())
                ->method('executeByManager')
                ->with($this->manager, $this->payload);
        $this->executeTask();
    }
    public function test_executeTask_inactiveManager_forbidden()
    {
        $this->manager->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTask(), 'Forbidden', 'only active manager can make this request');
    }
    
    //
    protected function registerActiveFreelanceSales()
    {
        $this->salesOne->expects($this->any())->method('getType')->willreturn(SalesType::FREELANCE);
        $this->salesTwo->expects($this->any())->method('getType')->willreturn(SalesType::FREELANCE);
        $this->manager->registerActiveFreelanceSales($this->assignmentPriorityCalculator);
    }
    public function test_registerActiveFreelanceSales_registerAllActiveFreelancer()
    {
        $matcher = $this->exactly(2);
        $this->assignmentPriorityCalculator->expects($matcher)
                ->method('registerSales')
                ->with(
                        $this->callback(function($arg) use($matcher) {
                            $this->assertEquals($arg, [$this->salesOne, $this->salesTwo][$matcher->numberOfInvocations() - 1]);
                            return true;
                            
                        })
                );
        $this->registerActiveFreelanceSales();
    }
    public function test_registerActiveFreelanceSales_containInactiveSales_excludeFromRegister()
    {
        $this->salesOne->expects($this->once())->method('isDisabled')->willReturn(true);
        $this->assignmentPriorityCalculator->expects($this->once())
                ->method('registerSales')
                ->with($this->salesTwo);
        $this->registerActiveFreelanceSales();
    }
    public function test_registerActiveFreelanceSales_containNonFreelance_excludeFromRegister()
    {
        $this->salesTwo->expects($this->once())->method('getType')->willReturn(SalesType::IN_HOUSE);
        $this->assignmentPriorityCalculator->expects($this->once())
                ->method('registerSales')
                ->with($this->salesOne);
        $this->registerActiveFreelanceSales();
    }
}

class TestableManager extends Manager
{
    public Personnel $personnel;
    public string $id = 'id';
    public bool $disabled = false;
    public Collection $salesCollection;
    
    public function __construct()
    {
        parent::__construct();
    }
}
