<?php

namespace Company\Domain\Model;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\AreaStructure\AreaData;
use DateTimeImmutable;
use SharedContext\Domain\ValueObject\Label;
use Tests\TestBase;

class AreaStructureTest extends TestBase
{
    protected $parent;
    protected $areaStructure;
    //
    protected $id = 'id';
    //
    protected $areaData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parent = $this->buildMockOfClass(AreaStructure::class);
        //
        $data = new AreaStructureData($this->createLabelData());
        $data->setId('id');
        $this->areaStructure = new TestableAreaStructure($data);
        $this->areaStructure->parent = $this->parent;
        //
        $this->areaData = (new AreaData($this->createLabelData()))->setId('areaId');
    }
    
    //
    protected function createAreaStructureData()
    {
        $data = new AreaStructureData($this->createLabelData());
        $data->setId($this->id);
        return $data;
    }
    
    //
    protected function construct()
    {
        return new TestableAreaStructure($this->createAreaStructureData());
    }
    public function test_construct_setProperties()
    {
        $areaStructure = $this->construct();
        $this->assertSame($this->id, $areaStructure->id);
        $this->assertFalse($areaStructure->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($areaStructure->createdTime);
        $this->assertInstanceOf(Label::class, $areaStructure->label);
        $this->assertNull($areaStructure->parent);
    }
    
    //
    protected function assertActive()
    {
        $this->areaStructure->assertActive();
    }
    public function test_assertActive_inactive_forbidden()
    {
        $this->areaStructure->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive area structure');
    }
    public function test_assertActive_active_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
    
    //
    protected function createChild()
    {
        return $this->areaStructure->createChild($this->createAreaStructureData());
    }
    public function test_createChild_setParent()
    {
        $child = $this->createChild();
        $this->assertSame($this->areaStructure, $child->parent);
    }
    
    //
    protected function createRootArea()
    {
        return $this->areaStructure->createRootArea($this->areaData);
    }
    public function test_createRootArea_returnArea()
    {
        $this->areaStructure->parent = null;
        $this->assertInstanceOf(Area::class, $this->createRootArea());
    }
    public function test_createRootArea_nonRootStructure_forbidden()
    {
        $this->assertRegularExceptionThrowed(
                fn() => $this->createRootArea(), 'Forbidden', 'can only create root area in active root structure');
    }
    
    //
    protected function isChildOf()
    {
        return $this->areaStructure->isChildOf($this->parent);
    }
    public function test_isChildOf_returnTrue()
    {
        $this->assertTrue($this->isChildOf());
    }
    public function test_isActiveChildOfParent_differentParent_returnFalse()
    {
        $this->areaStructure->parent = $this->buildMockOfClass(AreaStructure::class);
        $this->assertFalse($this->isChildOf());
    }
}

class TestableAreaStructure extends AreaStructure
{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public Label $label;
    public ?AreaStructure $parent;
}
