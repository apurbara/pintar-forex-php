<?php

namespace Company\Domain\Model\Manager;

use Company\Domain\Model\Manager;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Model\Manager\Sales\GreetingAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Model\Province\City;
use Company\Domain\Task\TaskInCompany;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesRole;
use Shared\Domain\ValueObject\AccountInfo;
use Tests\TestBase;
use TypeError;

class SalesTest extends TestBase
{

    protected $manager, $city;
    //
    protected $sales;
    protected MockObject $greetingAssignment, $factFindingAssignment, $strikingAssignment;
    //
    protected $id = 'newId', $salesType, $salesRole;
    //
    protected $payload = 'task payload', $salesTaskInCompany;
    //

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->city = $this->buildMockOfClass(City::class);
        //
        $data = (new SalesData())
                ->setRole(SalesRole::FACT_FINDER->value)
                ->setAccountInfoData($this->createAccountInfoData());
        $this->sales = new TestableSales($this->manager, $this->city, 'id', $data);
        
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
        $this->salesRole = SalesRole::STRIKER->value;
        //
        $this->salesTaskInCompany = $this->buildMockOfInterface(SalesTaskInCompany::class);
    }

    //
    protected function createSaleData()
    {
        return (new SalesData())
                ->setRole($this->salesRole)
                ->setAccountInfoData($this->createAccountInfoData());
    }
    
    //
    protected function construct()
    {
        return new TestableSales($this->manager, $this->city, $this->id, $this->createSaleData());
    }
    public function test_construct_setProperties()
    {
        $sales = $this->construct();
        $this->assertSame($this->manager, $sales->manager);
        $this->assertSame($this->city, $sales->city);
        $this->assertSame($this->id, $this->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($sales->createdTime);
        $this->assertNull($sales->contractTerminatedTime);
        $this->assertFalse($sales->contractTerminated);
        $this->assertEquals(SalesRole::from($this->salesRole), $sales->role);
        $this->assertInstanceOf(AccountInfo::class, $sales->accountInfo);
    }
    public function test_construct_assertManagerActive()
    {
        $this->manager->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_assertCityActive()
    {
        $this->city->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_emptyArea()
    {
        $this->city = null;
        $this->construct();
        $this->markAsSuccess();
    }
    
    //
    protected function terminateContract()
    {
        $this->greetingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->factFindingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->strikingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->sales->terminateContract();
    }
    public function test_terminateContract_setContractTerminatedAndTerminatedTime()
    {
        $this->terminateContract();
        $this->assertTrue($this->sales->contractTerminated);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($this->sales->contractTerminatedTime);
    }
    public function test_terminateContract_cancelActiveGreetingAssignment()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('cancelBySystem');
        $this->terminateContract();
    }
    public function test_terminateContract_ignoreInactiveGreetingAssignment()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::COMPLETED);
        $this->greetingAssignment->expects($this->never())
                ->method('cancelBySystem');
        $this->terminateContract();
    }
    public function test_terminateContract_cancelActiveFactFindingAssignment()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('cancelBySystem');
        $this->terminateContract();
    }
    public function test_terminateContract_ignoreInactiveFactFindingAssignment()
    {
        $this->factFindingAssignment->expects($this->once())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::COMPLETED);
        $this->factFindingAssignment->expects($this->never())
                ->method('cancelBySystem');
        $this->terminateContract();
    }
    public function test_terminateContract_cancelActiveStrikingAssignment()
    {
        $this->strikingAssignment->expects($this->once())
                ->method('cancelBySystem');
        $this->terminateContract();
    }
    public function test_terminateContract_ignoreInactiveStrikingAssignment()
    {
        $this->strikingAssignment->expects($this->once())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::COMPLETED);
        $this->strikingAssignment->expects($this->never())
                ->method('cancelBySystem');
        $this->terminateContract();
    }
    
    //
    protected function update()
    {
        $this->greetingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->factFindingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->strikingAssignment->expects($this->any())->method('getStatus')->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->sales->update($this->manager, $this->city, $this->createSaleData());
    }
    public function test_update_setProperties()
    {
        $this->sales->manager = $this->buildMockOfClass(Manager::class);
        $this->sales->city = null;
        $this->update();
        $this->assertSame($this->manager, $this->sales->manager);
        $this->assertSame($this->city, $this->sales->city);
        $this->assertEquals(SalesRole::from($this->salesRole), $this->sales->role);
    }
    public function test_update_assertManagerActive()
    {
        $this->manager->expects($this->once())
                ->method('assertActive');
        $this->update();
    }
    public function test_update_assertCityActive()
    {
        $this->city->expects($this->once())
                ->method('assertActive');
        $this->update();
    }
    public function test_update_emptyCity()
    {
        $this->city = null;
        $this->update();
        $this->markAsSuccess();
    }
    public function test_update_roleChange_cancelAllActiveAssignments()
    {
        $this->greetingAssignment->expects($this->once())
                ->method('cancelBySystem');
        $this->factFindingAssignment->expects($this->once())
                ->method('cancelBySystem');
        $this->strikingAssignment->expects($this->once())
                ->method('cancelBySystem');
        $this->update();
    }
    public function test_update_sameRole_ignoreCancellingAssignments()
    {
        $this->salesRole = $this->sales->role->value;
        $this->greetingAssignment->expects($this->never())
                ->method('cancelBySystem');
        $this->factFindingAssignment->expects($this->never())
                ->method('cancelBySystem');
        $this->strikingAssignment->expects($this->never())
                ->method('cancelBySystem');
        $this->update();
    }
    
    //
    protected function assertActive()
    {
        $this->sales->assertActive();
    }
    public function test_assertActive_contractTerminated_forbidden()
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
    protected function executeTaskInCompany()
    {
        $this->sales->executeTaskInCompany($this->salesTaskInCompany, $this->payload);
    }
    public function test_executeTaskInCompany_executeTask()
    {
        $this->salesTaskInCompany->expects($this->once())
                ->method('executeInCompany')
                ->with($this->payload);
        $this->executeTaskInCompany();
    }
    public function test_executeTaskInCompany_inactive()
    {
        $this->sales->contractTerminated = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTaskInCompany(), 'Forbidden', 'only active sales can make this request');
    }
    public function test_executeTaskInCompany_inactiveSales_forbidden()
    {
        $this->sales->contractTerminated = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTaskInCompany(), 'Forbidden', 'only active sales can make this request');
    }
    public function test_executeTaskInCompany_notSalesTask_typeError()
    {
        $this->salesTaskInCompany = $this->buildMockOfClass(TaskInCompany::class);
        $this->expectException(TypeError::class);
        $this->executeTaskInCompany();
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
    public ?City $city;
    public string $id;
    public bool $contractTerminated;
    public DateTimeImmutable $createdTime;
    public ?DateTimeImmutable $contractTerminatedTime;
    public SalesRole $role;
    public AccountInfo $accountInfo;
    public Collection $greetingAssignments;
    public Collection $factFindingAssignments;
    public Collection $strikingAssignments;
    //
    public ?int $activeCustomerAssignmentCount = null;
}
