<?php

namespace Company\Domain\Model\Personnel;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Model\Personnel\Manager\SalesData;
use DateTimeImmutable;
use Tests\TestBase;

class ManagerTest extends TestBase
{

    protected $personnel;
    protected $manager;
    //
    protected $id = 'newId';
    //
    protected $area, $salesData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->personnel = $this->buildMockOfClass(Personnel::class);
        $this->manager = new TestableManager($this->personnel, (new ManagerData())->setId('id'));
        //
        $this->area = $this->buildMockOfClass(Area::class);
        $this->salesData = (new SalesData('IN_HOUSE'))->setId('salesId');
    }

    //
    protected function createManagerData()
    {
        return (new ManagerData())
                        ->setId($this->id);
    }

    //
    protected function construct()
    {
        return new TestableManager($this->personnel, $this->createManagerData());
    }
    public function test_construct_setProperties()
    {
        $manager = $this->construct();
        $this->assertSame($this->personnel, $manager->personnel);
        $this->assertSame($this->id, $manager->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($manager->createdTime);
        $this->assertFalse($manager->disabled);
    }
    
    //
    protected function assertActive()
    {
        $this->manager->assertActive();
    }
    public function test_assertActive_inactiveManager_forbidden()
    {
        $this->manager->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive manager');
    }
    public function test_assertActive_activeManager_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
    
    //
    protected function assignPersonnelAsSales()
    {
        return $this->manager->assignPersonnelAsSales($this->personnel, $this->area, $this->salesData);
    }
    public function test_assignPersonnelAsManager_returnSales()
    {
        $this->assertInstanceOf(Sales::class, $this->assignPersonnelAsSales());
    }
}

class TestableManager extends Manager
{

    public Personnel $personnel;
    public string $id;
    public DateTimeImmutable $createdTime;
    public bool $disabled;
}
