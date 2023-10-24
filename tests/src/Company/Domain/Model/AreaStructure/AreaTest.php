<?php

namespace Company\Domain\Model\AreaStructure;

use Company\Domain\Model\AreaStructure;
use DateTimeImmutable;
use SharedContext\Domain\ValueObject\Label;
use Tests\TestBase;

class AreaTest extends TestBase
{

    protected $areaStructure;
    protected $childAreaStructure;
    protected $area;
    //
    protected $id = 'newId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->areaStructure = $this->buildMockOfClass(AreaStructure::class);
        $this->childAreaStructure = $this->buildMockOfClass(AreaStructure::class);
        //
        $data = (new AreaData($this->createLabelData()))->setId('id');
        $this->area = new TestableArea($this->areaStructure, $data);
    }

    //
    protected function createAreaData()
    {
        return (new AreaData($this->createLabelData()))
                        ->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableArea($this->areaStructure, $this->createAreaData());
    }
    public function test_construct_setProperties()
    {
        $area = $this->construct();
        $this->assertSame($this->areaStructure, $area->areaStructure);
        $this->assertSame($this->id, $area->id);
        $this->assertFalse($area->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($area->createdTime);
        $this->assertInstanceOf(Label::class, $area->label);
        $this->assertNull($area->parent);
    }
    
    //
    protected function createChild()
    {
        $this->childAreaStructure->expects($this->any())
                ->method('isChildOf')
                ->willReturn(true);
        return $this->area->createChild($this->childAreaStructure, $this->createAreaData());
    }
    public function test_createChild_setParent()
    {
        $child = $this->createChild();
        $this->assertSame($this->area, $child->parent);
    }
    public function test_createChild_assertChildStructureIsActiveChildOfAreaStructure()
    {
        $this->childAreaStructure->expects($this->once())
                ->method('isChildOf')
                ->with($this->areaStructure)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->createChild(), 'Forbidden', 'child area must associate with active structure descendant');
    }
    
    //
    protected function assertActive()
    {
        $this->area->assertActive();
    }
    public function test_assertActive_inactiveArea_forbidden()
    {
        $this->area->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), "Forbidden", 'inactive area');
    }
    public function test_assertActive_activeArea_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
}

class TestableArea extends Area
{

    public AreaStructure $areaStructure;
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public Label $label;
    public ?Area $parent;
}
