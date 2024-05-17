<?php

namespace Company\Domain\Model;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\AreaStructure\AreaData;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SharedContext\Domain\ValueObject\Label;
use Tests\TestBase;

class AreaStructureTest extends TestBase
{
    protected $areaStructure, $parent, $child, $area;
    //
    protected $id = 'id';
    //
    protected $areaData;

    protected function setUp(): void
    {
        parent::setUp();
        //
        $data = new AreaStructureData($this->createLabelData());
        $data->setId('id');
        $this->areaStructure = new TestableAreaStructure($data);
        $this->areaStructure->children = new ArrayCollection();
        $this->areaStructure->areas = new ArrayCollection();
        
        $this->parent = $this->buildMockOfClass(AreaStructure::class);
        $this->child = $this->buildMockOfClass(AreaStructure::class);
        $this->area = $this->buildMockOfClass(Area::class);
        
        $this->areaStructure->parent = $this->parent;
        $this->areaStructure->children->add($this->child);
        $this->areaStructure->areas->add($this->area);
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
    protected function update()
    {
        $this->areaStructure->update($this->createAreaStructureData());
    }
    public function test_update_updateLabel()
    {
        $this->areaStructure->label = $this->buildMockOfClass(Label::class);
        $this->update();
        $this->assertEquals(new Label($this->createLabelData()), $this->areaStructure->label);
    }
    
    //
    protected function disable()
    {
        $this->child->expects($this->any())
                ->method('isDisabled')
                ->willReturn(true);
        $this->area->expects($this->any())
                ->method('isDisabled')
                ->willReturn(true);
        $this->areaStructure->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->areaStructure->disabled);
    }
    public function test_disable_containActiveChildren_forbidden()
    {
        $this->child->expects($this->once())
                ->method('isDisabled')
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->disable(), 'Forbidden', 'area structure has active children');
    }
    public function test_disable_containActiveArea_forbidden()
    {
        $this->area->expects($this->once())
                ->method('isDisabled')
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->disable(), 'Forbidden', 'area structure has active area');
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
    public function test_createChild_inactiveAreaStructure_forbidden()
    {
        $this->areaStructure->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->createChild(), 'Forbidden', 'inactive area structure');
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
    public Collection $children;
    public Collection $areas;
}
