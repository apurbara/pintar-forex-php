<?php

namespace Company\Domain\Model\AreaStructure;

use Company\Domain\Model\AreaStructure;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SharedContext\Domain\ValueObject\Label;
use Tests\TestBase;

class AreaTest extends TestBase
{

    protected $areaStructure;
    protected $childAreaStructure;
    protected $area, $child;
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
        
        $this->child = $this->buildMockOfClass(Area::class);
        $this->area->children = new ArrayCollection();
        $this->area->children->add($this->child);
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
    public function test_construct_assertAreaStructureActive()
    {
        $this->areaStructure->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    
    //
    protected function update()
    {
        $this->area->update($this->createAreaData());
    }
    public function test_update_updateLabel()
    {
        $this->area->label = $this->buildMockOfClass(Label::class);
        $this->update();
        $this->assertEquals(new Label($this->createLabelData()), $this->area->label);
    }
    
    //
    protected function disable()
    {
        $this->child->expects($this->any())
                ->method('isDisabled')
                ->willReturn(true);
        $this->area->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->area->disabled);
    }
    public function test_disable_hasActiveChildren_forbidden()
    {
        $this->child->expects($this->once())
                ->method('isDisabled')
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->disable(), 'Forbidden', 'area has active child');
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
    public function test_createChild_inactiveArea_forbidden()
    {
        $this->area->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->createChild(), 'Forbidden', 'inactive area');
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
    public Collection $children;
}
