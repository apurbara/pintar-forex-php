<?php

namespace Company\Domain\Model\Manager;

use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Province\City;
use Company\Domain\Task\TaskInCompany;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesRole;
use Shared\Domain\Enum\SalesType;
use Shared\Domain\Event\MultipleCustomerAssignmentReceivedBySales;
use Shared\Domain\ValueObject\AccountInfo;
use Tests\TestBase;
use TypeError;

class SalesTest extends TestBase
{

    protected $manager, $city;
    //
    protected $sales;
    protected MockObject $customerAssignmentOne, $customerAssignmentTwo;
    //
    protected $id = 'newId', $salesType, $salesRole;
    protected $customerAssignmentId = 'customerAssignmentId', $customer, $customerJourney;
    //
    protected $payload = 'task payload', $salesTaskInCompany;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->city = $this->buildMockOfClass(City::class);
        //
        $data = (new SalesData())
                ->setType(SalesType::IN_HOUSE->value)
                ->setRole(SalesRole::FACT_FINDER->value)
                ->setAccountInfoData($this->createAccountInfoData());
        $this->sales = new TestableSales($this->manager, $this->city, 'id', $data);
        
        $this->customerAssignmentOne = $this->buildMockOfClass(CustomerAssignment::class);
        $this->customerAssignmentTwo = $this->buildMockOfClass(CustomerAssignment::class);
        
        $this->sales->customerAssignments = new ArrayCollection();
        $this->sales->customerAssignments->add($this->customerAssignmentOne);
        $this->sales->customerAssignments->add($this->customerAssignmentTwo);
        //
        $this->salesType = SalesType::FREELANCE->value;
        $this->salesRole = SalesRole::STRIKER->value;
        //
        $this->customer = $this->buildMockOfClass(Customer::class);
        $this->customerJourney = $this->buildMockOfClass(CustomerJourney::class);
        //
        $this->salesTaskInCompany = $this->buildMockOfInterface(SalesTaskInCompany::class);
    }

    //
    protected function createSaleData()
    {
        return (new SalesData())
                ->setType($this->salesType)
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
        $this->assertEquals(SalesType::from($this->salesType), $sales->type);
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
        $this->customerAssignmentOne->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->customerAssignmentTwo->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->sales->terminateContract();
    }
    public function test_terminateContract_setContractTerminatedAndTerminatedTime()
    {
        $this->terminateContract();
        $this->assertTrue($this->sales->contractTerminated);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($this->sales->contractTerminatedTime);
    }
    public function test_terminateContract_cancelActiveAssignment()
    {
        $this->customerAssignmentOne->expects($this->once())
                ->method('cancelBySystem');
        $this->customerAssignmentTwo->expects($this->once())
                ->method('cancelBySystem');
        $this->terminateContract();
    }
    public function test_terminateContract_ignoreInactiveAssignment()
    {
        $this->customerAssignmentOne->expects($this->once())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::COMPLETED);
        $this->customerAssignmentOne->expects($this->never())
                ->method('cancelBySystem');
        $this->terminateContract();
    }
    
    //
    protected function update()
    {
        $this->sales->update($this->manager, $this->city, $this->createSaleData());
    }
    public function test_update_setProperties()
    {
        $this->sales->manager = $this->buildMockOfClass(Manager::class);
        $this->sales->city = null;
        $this->sales->type = SalesType::IN_HOUSE;
        $this->update();
        $this->assertSame($this->manager, $this->sales->manager);
        $this->assertSame($this->city, $this->sales->city);
        $this->assertEquals(SalesType::from($this->salesType), $this->sales->type);
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
    
    protected function calculateActiveCustomerAssignmentsCount()
    {
        $this->customerAssignmentOne->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->customerAssignmentTwo->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        return $this->sales->calculateActiveCustomerAssignmentsCount();
    }
    public function test_calculateActiveCustomerAssignmentsCount_returnCustomerAssignmentCount()
    {
        $this->assertEquals(2, $this->calculateActiveCustomerAssignmentsCount());
    }
    public function test_calculateActiveCustomerAssignmentsCount_containInactiveAssignment_returnActiveCustomerAssignmentCount()
    {
        $this->customerAssignmentTwo->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::CANCELLED);
        $this->assertEquals(1, $this->calculateActiveCustomerAssignmentsCount());
    }
    public function test_calculateActiveCustomerAssignmentsCount_setActiveAssignmentCount()
    {
        $this->calculateActiveCustomerAssignmentsCount();
        $this->assertEquals(2, $this->sales->activeCustomerAssignmentCount);
    }
    public function test_calculateActiveCustomerAssignmentsCount_activeAssignmentCountAlreadyExist_returnExistingCount()
    {
        $this->sales->activeCustomerAssignmentCount = 5;
        $this->assertEquals(5, $this->calculateActiveCustomerAssignmentsCount());
    }
    
    //
    protected function receiveCustomerAssignment()
    {
        return $this->sales->receiveCustomerAssignment($this->customerAssignmentId, $this->customer, $this->customerJourney);
    }
    public function test_receiveCustomerAssignment_returnCustomerAssignment()
    {
        $this->assertInstanceOf(CustomerAssignment::class, $this->receiveCustomerAssignment());
    }
    public function test_receiveCustomerAssignment_storeEvent()
    {
        $this->receiveCustomerAssignment();
        $event = (new MultipleCustomerAssignmentReceivedBySales($this->sales->id))
                ->addCustomerAssignmentId($this->customerAssignmentId);
        $this->assertEquals($event, $this->sales->recordedEvents[0]);
    }
    public function test_receiveCustomerAssignment_consecutiveAssignmentReceived_storeEvent()
    {
        $otherCustomer = $this->buildMockOfClass(Customer::class);
        $this->sales->receiveCustomerAssignment($customerAssignmentId = 'assignedCusstomerId', $this->customer, $this->customerJourney);
        $this->sales->receiveCustomerAssignment($otherCustomerAssignmentId = 'otherCustomerAssignmentId', $otherCustomer, $this->customerJourney);
        $event = (new MultipleCustomerAssignmentReceivedBySales($this->sales->id))
                ->addCustomerAssignmentId($customerAssignmentId)
                ->addCustomerAssignmentId($otherCustomerAssignmentId);
        $this->assertEquals($event, $this->sales->recordedEvents[0]);
    }
    public function test_receiveCustomerAssignment_consecutiveAssignmentReceived_storeOnlySingleEvent()
    {
        $otherCustomer = $this->buildMockOfClass(Customer::class);
        $this->sales->receiveCustomerAssignment($customerAssignmentId = 'assignedCusstomerId', $this->customer, $this->customerJourney);
        $this->sales->receiveCustomerAssignment($otherCustomerAssignmentId = 'otherCustomerAssignmentId', $otherCustomer, $this->customerJourney);
        $event = (new MultipleCustomerAssignmentReceivedBySales($this->sales->id))
                ->addCustomerAssignmentId($customerAssignmentId)
                ->addCustomerAssignmentId($otherCustomerAssignmentId);
        $this->assertEquals($event, $this->sales->recordedEvents[0]);
        $this->assertEquals(1, count($this->sales->recordedEvents));
    }
    public function test_receiveCustomerAssignment_incrementActiveAssignmentValue()
    {
        $this->receiveCustomerAssignment();
        $this->assertSame(1, $this->sales->activeCustomerAssignmentCount);
        $this->sales->activeCustomerAssignmentCount = 3;
        $this->receiveCustomerAssignment();
        $this->assertSame(4, $this->sales->activeCustomerAssignmentCount);
    }
    public function test_receiveCustomerAssignment_consecutiveAssignmentReceived_aCustomerAlreadyHadled_ignoreAssignment()
    {
        $this->sales->activeCustomerAssignmentCount = 3;
        $this->customer->expects($this->any())
                ->method('hasActiveAssignment')
                ->willReturn(true);
        $this->receiveCustomerAssignment();
        $otherCustomer = $this->buildMockOfClass(Customer::class);
        $this->sales->receiveCustomerAssignment($otherCustomerAssignmentId = 'otherCustomerAssignmentId', $otherCustomer, $this->customerJourney);
        $event = (new MultipleCustomerAssignmentReceivedBySales($this->sales->id))
                ->addCustomerAssignmentId($otherCustomerAssignmentId);
        $this->assertEquals($event, $this->sales->recordedEvents[0]);
        $this->assertSame(4, $this->sales->activeCustomerAssignmentCount);
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
    public SalesType $type;
    public SalesRole $role;
    public AccountInfo $accountInfo;
    public Collection $customerAssignments;
    //
    public $recordedEvents;
    //
    public ?int $activeCustomerAssignmentCount = null;
    public ?MultipleCustomerAssignmentReceivedBySales $multipleCustomerAssignmentReceivedBySalesEvent;
}
