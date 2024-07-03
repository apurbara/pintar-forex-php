<?php

namespace Manager\Domain\Model\Manager;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use PHPUnit\Framework\MockObject\MockObject;
use Resources\Exception\RegularException;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\SalesType;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;
use SharedContext\Domain\ValueObject\AccountInfo;
use Tests\TestBase;

class SalesTest extends TestBase
{

    protected $sales, $manager;
    protected MockObject $customerAssignmentOne, $customerAssignmentTwo;
    //
    protected $id = 'newId', $salesType;
    protected $customerAssignmentId = 'customerAssignmentId', $customer, $customerJourney;
    //
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = new TestableSales();
        
        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->sales->manager = $this->manager;
        
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
    public Manager $manager;
    public string $id = 'id';
    public DateTimeImmutable $createdTime;
    public ?DateTimeImmutable $cancelTime;
    public bool $cancelled = false;
    public SalesType $type = SalesType::IN_HOUSE;
    public AccountInfo $accountInfo;
    public Collection $customerAssignments;
    //
    public $recordedEvents;
    //
    public ?int $activeCustomerAssignmentCount = null;
    public ?MultipleCustomerAssignmentReceivedBySales $multipleCustomerAssignmentReceivedBySalesEvent;
    
    function __construct()
    {
        parent::__construct();
    }
}
