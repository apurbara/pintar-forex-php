<?php

namespace Company\Domain\Model;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\Sales\CustomerAssignment;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Resources\Exception\RegularException;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;
use SharedContext\Domain\ValueObject\AccountInfo;
use Tests\TestBase;

class SalesTest extends TestBase
{

    protected $area;
    //
    protected $sales;
    protected MockObject $customerAssignmentOne, $customerAssignmentTwo;
    //
    protected $id = 'newId', $salesType;
    protected $customerAssignmentId = 'customerAssignmentId', $customer, $customerJourney;
    //
    protected $payload = 'task payload', $salesTaskInCompany;

    protected function setUp(): void
    {
        parent::setUp();
        $this->area = $this->buildMockOfClass(Area::class);
        //
        $data = (new SalesData())
                ->setType(SalesType::IN_HOUSE->value)
                ->setAccountInfoData($this->createAccountInfoData());
        $this->sales = new TestableSales($this->area, 'id', $data);
        
        $this->customerAssignmentOne = $this->buildMockOfClass(CustomerAssignment::class);
        $this->customerAssignmentTwo = $this->buildMockOfClass(CustomerAssignment::class);
        
        $this->sales->customerAssignments = new ArrayCollection();
        $this->sales->customerAssignments->add($this->customerAssignmentOne);
        $this->sales->customerAssignments->add($this->customerAssignmentTwo);
        //
        $this->salesType = SalesType::FREELANCE->value;
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
                ->setAccountInfoData($this->createAccountInfoData());
    }
    
    //
    protected function construct()
    {
        return new TestableSales($this->area, $this->id, $this->createSaleData());
    }
    public function test_construct_setProperties()
    {
        $sales = $this->construct();
        $this->assertSame($this->area, $sales->area);
        $this->assertSame($this->id, $this->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($sales->createdTime);
        $this->assertNull($sales->cancelTime);
        $this->assertFalse($sales->cancelled);
        $this->assertEquals(SalesType::from($this->salesType), $sales->type);
        $this->assertInstanceOf(AccountInfo::class, $sales->accountInfo);
    }
    public function test_construct_assertAreaActive()
    {
        $this->area->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_emptyArea()
    {
        $this->area = null;
        $this->construct();
        $this->markAsSuccess();
    }
    
    //
    protected function cancel()
    {
        $this->customerAssignmentOne->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->customerAssignmentTwo->expects($this->any())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::ACTIVE);
        $this->sales->cancel();
    }
    public function test_cancel_setCancelledAndCancelTime()
    {
        $this->cancel();
        $this->assertTrue($this->sales->cancelled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($this->sales->cancelTime);
    }
    public function test_cancel_cancelActiveAssignment()
    {
        $this->customerAssignmentOne->expects($this->once())
                ->method('cancel');
        $this->customerAssignmentTwo->expects($this->once())
                ->method('cancel');
        $this->cancel();
    }
    public function test_cancel_ignoreInactiveAssignment()
    {
        $this->customerAssignmentOne->expects($this->once())
                ->method('getStatus')
                ->willReturn(CustomerAssignmentStatus::RECYCLED);
        $this->customerAssignmentOne->expects($this->never())
                ->method('cancel');
        $this->cancel();
    }
    
    //
    protected function isInHouseSales()
    {
        return $this->sales->isInHouseSales();
    }
    public function test_isInHouseSales_inHouseSales_returnTrue()
    {
        $this->assertTrue($this->isInHouseSales());
    }
    public function test_isInHouseSales_freelanceSales_returnFalse()
    {
        $this->sales->type = SalesType::FREELANCE;
        $this->assertFalse($this->isInHouseSales());
    }
    
    //
    protected function assertActive()
    {
        $this->sales->assertActive();
    }
    public function test_assertActive_cancelledSales_forbidden()
    {
        $this->sales->cancelled = true;
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
    public function test_executeTaskInCompany_forbidden()
    {
        $this->sales->cancelled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->executeTaskInCompany(), 'Forbidden', 'only active sales can make this request');
    }
    
    //
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
    public function test_receiveCustomerAssignment_consecutiveAssignmentReceived_assignmentCausedError_ignoreFailAssignment()
    {
        $this->sales->activeCustomerAssignmentCount = 3;
        $this->customer->expects($this->any())
                ->method('assertHasNoActiveAssignment')
                ->willThrowException(RegularException::forbidden('unassignable customer'));
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
    public ?Area $area;
    public string $id;
    public DateTimeImmutable $createdTime;
    public ?DateTimeImmutable $cancelTime;
    public bool $cancelled;
    public SalesType $type;
    public AccountInfo $accountInfo;
    public Collection $customerAssignments;
    //
    public $recordedEvents;
    //
    public ?int $activeCustomerAssignmentCount = null;
    public ?MultipleCustomerAssignmentReceivedBySales $multipleCustomerAssignmentReceivedBySalesEvent;
}
