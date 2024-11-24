<?php

namespace Manager\Domain\Model\Manager;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Model\Manager\Sales\GreetingAssignment;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment;
use PHPUnit\Framework\MockObject\MockObject;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesRole;
use Shared\Domain\ValueObject\AccountInfo;
use Tests\TestBase;

class SalesTest extends TestBase
{

    protected $sales, $manager;
    protected MockObject $greetingAssignment, $factFindingAssignment, $strikingAssignment;
    //
    protected $id = 'newId', $salesType;
    protected $customerAssignmentId = 'customerAssignmentId', $customer, $customerJourney;
    //
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = new TestableSales();
        $this->sales->role = SalesRole::FACT_FINDER;
        
        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->sales->manager = $this->manager;
        
        $this->greetingAssignment = $this->buildMockOfClass(GreetingAssignment::class);
        $this->sales->greetingAssignments = new ArrayCollection();
        $this->sales->greetingAssignments->add($this->greetingAssignment);
        
        $this->factFindingAssignment = $this->buildMockOfClass(FactFindingAssignment::class);
        $this->sales->factFindingAssignments = new ArrayCollection();
        $this->sales->factFindingAssignments->add($this->factFindingAssignment);
        
        $this->strikingAssignment = $this->buildMockOfClass(StrikingAssignment::class);
        $this->sales->strikingAssignments = new ArrayCollection();
        $this->sales->strikingAssignments->add($this->strikingAssignment);
        //
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
    }
    
    //
    protected function assertActive()
    {
        $this->sales->assertActive();
    }
    public function test_assertActive_cancelledSales_forbidden()
    {
        $this->sales->contractTerminated = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive sales');
    }
    public function test_assertActive_activeSales_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
    
    //
    protected function assertRoleEquals()
    {
        $this->sales->assertRoleEquals(SalesRole::FACT_FINDER);
    }
    public function test_assertRoleEquals_differentRole_forbidden()
    {
        $this->sales->role = SalesRole::GREETER;
        $this->assertRegularExceptionThrowed(fn() => $this->assertRoleEquals(), 'Forbidden', 'unmatch role');
    }
    public function test_assertRoleEquals_sameRole_void()
    {
        $this->assertRoleEquals();
        $this->markAsSuccess();
    }
    
    //
    protected function belongsToManager()
    {
        return $this->sales->belongsToManager($this->manager);
    }
    public function test_belongsToManager_sameManager_returnTrue()
    {
        $this->assertTrue($this->belongsToManager());
    }
    public function test_belongsToManager_differentManager_returnFalse()
    {
        $this->sales->manager = $this->buildMockOfClass(Manager::class);
        $this->assertFalse($this->belongsToManager());
    }
    
    //
    protected function assertBelongsToManager()
    {
        $this->sales->assertBelongsToManager($this->manager);
    }
    public function test_assertBelongsToManager_differentManager_forbidden()
    {
        $this->sales->manager = $this->buildMockOfClass(Manager::class);
        $this->assertRegularExceptionThrowed(fn() => $this->assertBelongsToManager(), 'Forbidden', 'sales does not belongs to manager');
    }
    public function test_assertBelongsToManager_sameManager_void()
    {
        $this->assertBelongsToManager();
        $this->markAsSuccess();
    }
    
    //
    protected function calculateActiveCustomerAssignmentsCount()
    {
        $this->greetingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->factFindingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->strikingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        return $this->sales->calculateActiveCustomerAssignmentsCount();
    }
    public function test_calculateActiveCustomerAssignmentsCount_returnFactFindingAssignmentCount()
    {
        $this->assertEquals(1, $this->calculateActiveCustomerAssignmentsCount());
    }
    public function test_calculateActiveCustomerAssignmentsCount_containInactiveAssignment_excludeInactiveAssignmentCount()
    {
        $this->factFindingAssignment->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::CANCELLED);
        $this->assertEquals(0, $this->calculateActiveCustomerAssignmentsCount());
    }
    public function test_calculateActiveCustomerAssignmentsCount_greeterRole_returnGreetingAssignmentCount()
    {
        $this->sales->role = SalesRole::GREETER;
        $this->strikingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM);
        $this->factFindingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM);
        $this->assertEquals(1, $this->calculateActiveCustomerAssignmentsCount());
    }
    public function test_calculateActiveCustomerAssignmentsCount_strikerRole_returnGreetingAssignmentCount()
    {
        $this->sales->role = SalesRole::STRIKER;
        $this->greetingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM);
        $this->factFindingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::CANCELLED_BY_SYSTEM);
        $this->assertEquals(1, $this->calculateActiveCustomerAssignmentsCount());
    }
    public function test_calculateActiveCustomerAssignmentsCount_setActiveAssignmentCount()
    {
        $this->calculateActiveCustomerAssignmentsCount();
        $this->assertEquals(1, $this->sales->activeCustomerAssignmentCount);
    }
    public function test_calculateActiveCustomerAssignmentsCount_activeAssignmentCountAlreadyExist_returnExistingCount()
    {
        $this->sales->activeCustomerAssignmentCount = 5;
        $this->assertEquals(5, $this->calculateActiveCustomerAssignmentsCount());
    }
    
    //
    protected function incrementActiveAssignmentCount()
    {
        $this->sales->activeCustomerAssignmentCount = 3;
        $this->sales->incrementActiveAssignmentCount();
    }
    public function test_incrementActiveAssignmentCount_incrementActiveAssignmentCount()
    {
        $this->incrementActiveAssignmentCount();
        $this->assertEquals(4, $this->sales->activeCustomerAssignmentCount);
    }
}

class TestableSales extends Sales
{
    public Manager $manager;
    public string $id = 'id';
    public DateTimeImmutable $createdTime;
    public ?DateTimeImmutable $contractTerminatedTime;
    public bool $contractTerminated = false;
    public AccountInfo $accountInfo;
    public SalesRole $role;
    public Collection $greetingAssignments;
    public Collection $factFindingAssignments;
    public Collection $strikingAssignments;
    //
    public ?int $activeCustomerAssignmentCount = null;
    
    function __construct()
    {
        parent::__construct();
    }
}
