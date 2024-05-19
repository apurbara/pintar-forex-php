<?php

namespace Company\Domain\Model\Personnel;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use DateTimeImmutable;
use SharedContext\Domain\Enum\SalesType;
use Tests\TestBase;

class SalesTest extends TestBase
{

    protected $personnel;
    protected $area;
    //
    protected $sales;
//    protected $customerAssignment;
    //
    protected $id = 'newId', $salesType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->personnel = $this->buildMockOfClass(Personnel::class);
        $this->area = $this->buildMockOfClass(Area::class);
        //
        $data = new SalesData(SalesType::FREELANCE->value);
        $this->sales = new TestableSales($this->personnel, $this->area, 'id', $data);
        
//        $this->customerAssignment = $this->buildMockOfClass(CustomerAssignment::class);
//        $this->sales->customerAssignments = new ArrayCollection();
//        $this->sales->customerAssignments->add($this->customerAssignment);
        //
        $this->salesType = SalesType::IN_HOUSE->value;
    }

    //
    protected function createSaleData()
    {
        return new SalesData($this->salesType);
    }
    
    //
    protected function construct()
    {
        return new TestableSales($this->personnel, $this->area, $this->id, $this->createSaleData());
    }
    public function test_construct_setProperties()
    {
        $sales = $this->construct();
        $this->assertSame($this->personnel, $sales->personnel);
        $this->assertSame($this->area, $sales->area);
        $this->assertSame($this->id, $this->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($sales->createdTime);
        $this->assertFalse($sales->disabled);
        $this->assertEquals(SalesType::from($this->salesType), $sales->type);
    }
    public function test_construct_assertPersonnelActive()
    {
        $this->personnel->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    public function test_construct_assertAreaActive()
    {
        $this->area->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    
    //
    protected function disable()
    {
        $this->sales->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->sales->disabled);
    }
//    public function test_disable_cancelActiveAssignment()
//    {
//        $this->customerAssignment->expects($this->once())
//                ->method('cancel');
//        $this->disable();
//    }
//    public function test_disable_ignoreInactiveAssignment()
//    {
//        $this->customerAssignment->expects($this->once())
//                ->method('isCancelled')
//                ->willReturn(true);
//        $this->customerAssignment->expects($this->never())
//                ->method('cancel');
//        $this->disable();
//    }
    
    //
    protected function enable()
    {
        $this->sales->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->sales->disabled = true;
        $this->enable();
        $this->assertFalse($this->sales->disabled);
    }
}

class TestableSales extends Sales
{

    public Personnel $personnel;
    public Area $area;
    public string $id;
    public DateTimeImmutable $createdTime;
    public bool $disabled;
    public SalesType $type;
//    public Collection $customerAssignments;
}
