<?php

namespace Company\Domain\Model\Manager;

use Company\Domain\Model\Manager;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Province\City;
use Company\Domain\Task\TaskInCompany;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\SalesType;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;
use SharedContext\Domain\ValueObject\AccountInfo;
use Tests\TestBase;

class SalesTest extends TestBase
{

    protected $manager, $city;
    //
    protected $sales;
    protected MockObject $customerAssignmentOne, $customerAssignmentTwo;
    //
    protected $id = 'newId', $salesType;
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
                ->setAccountInfoData($this->createAccountInfoData());
        $this->sales = new TestableSales($this->manager, $this->city, 'id', $data);
        
        $this->customerAssignmentOne = $this->buildMockOfClass(CustomerAssignment::class);
        $this->customerAssignmentTwo = $this->buildMockOfClass(CustomerAssignment::class);
        
        $this->sales->customerAssignments = new ArrayCollection();
        $this->sales->customerAssignments->add($this->customerAssignmentOne);
        $this->sales->customerAssignments->add($this->customerAssignmentTwo);
        //
        $this->salesType = SalesType::FREELANCE->value;
        //
        $this->salesTaskInCompany = $this->buildMockOfInterface(SalesTaskInCompany::class);
    }

    //
    protected function createSaleData()
    {
        return (new SalesData())
                ->setType($this->salesType)
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
                ->willReturn(CustomerAssignmentStatus::RECYCLED);
        $this->customerAssignmentOne->expects($this->never())
                ->method('cancelBySystem');
        $this->terminateContract();
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
        $this->expectException(\TypeError::class);
        $this->executeTaskInCompany();
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
    public AccountInfo $accountInfo;
    public Collection $customerAssignments;
    //
    public $recordedEvents;
    //
    public ?int $activeCustomerAssignmentCount = null;
    public ?MultipleCustomerAssignmentReceivedBySales $multipleCustomerAssignmentReceivedBySalesEvent;
}
