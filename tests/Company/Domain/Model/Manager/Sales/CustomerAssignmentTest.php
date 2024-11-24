<?php

namespace Company\Domain\Model\Manager\Sales;

use Company\Domain\Model\Manager\Sales\CustomerAssignment\SalesActivitySchedule;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Shared\Domain\Enum\SalesActivityScheduleStatus;
use Tests\TestBase;

class CustomerAssignmentTest extends TestBase
{
    protected $customerAssignment;
    protected $id = 'newId';
    
    protected $salesActivitySchedule;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAssignment = new TestableCustomerAssignment('id');
        
        $this->salesActivitySchedule = $this->buildMockOfClass(SalesActivitySchedule::class);
        $this->customerAssignment->salesActivitySchedules = new ArrayCollection();
        $this->customerAssignment->salesActivitySchedules->add($this->salesActivitySchedule);
    }
    
    //
    protected function construct()
    {
        return new TestableCustomerAssignment($this->id);
    }
    public function test_construct_setProperties()
    {
        $customerAssignment = $this->construct();
        $this->assertSame($this->id, $customerAssignment->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($customerAssignment->createdTime);
    }
    
    //
    protected function cancelAllActiveSchedule()
    {
        $this->salesActivitySchedule->expects($this->any())
                ->method('getStatus')
                ->willReturn(SalesActivityScheduleStatus::SCHEDULED);
        $this->customerAssignment->cancelAllActiveSchedule();
    }
    public function test_cancelAllActiveSchedule_cancelScheduleBySystem()
    {
        $this->salesActivitySchedule->expects($this->once())
                ->method('cancelBySystem');
        $this->cancelAllActiveSchedule();
    }
    public function test_cancelAllActiveSchedule_containConcludedSchedule_ignoreConcludedSchedule()
    {
        $this->salesActivitySchedule->expects($this->any())
                ->method('getStatus')
                ->willReturn(SalesActivityScheduleStatus::COMPLETED);
        $this->salesActivitySchedule->expects($this->never())
                ->method('cancelBySystem');
        $this->cancelAllActiveSchedule();
    }
}

class TestableCustomerAssignment extends CustomerAssignment
{
    public string $id;
    public DateTimeImmutable $createdTime;
    public Collection $salesActivitySchedules;
}
