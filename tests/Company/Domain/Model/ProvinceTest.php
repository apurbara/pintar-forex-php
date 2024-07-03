<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use Tests\TestBase;

class ProvinceTest extends TestBase
{

    protected $province;
    protected $id = 'newId', $name = 'new Province';

    protected function setUp(): void
    {
        parent::setUp();
        $data = (new ProvinceData())
                ->setName('province name');
        $this->province = new TestableProvince('id', $data);
    }

    //
    protected function getData()
    {
        return (new ProvinceData())
                        ->setName($this->name);
    }
    
    //
    protected function construct()
    {
        return new TestableProvince($this->id, $this->getData());
    }
    public function test_construct_setProperties()
    {
        $province = $this->construct();
        $this->assertSame($this->id, $province->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($province->createdTime);
        $this->assertFalse($province->disabled);
        $this->assertSame($this->name, $province->name);
    }
    public function test_construct_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'province name is mandatory');
    }
    
    //
    protected function update()
    {
        $this->province->update($this->getData());
    }
    public function test_update_update_updateProperties()
    {
        $this->update();
        $this->assertSame($this->name, $this->province->name);
    }
    public function test_update_update_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'province name is mandatory');
    }
    
    //
    protected function disable()
    {
        $this->province->disable();
    }
    public function test_disabled_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->province->disabled);
    }
    
    //
    protected function enable()
    {
        $this->province->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->province->disabled = true;
        $this->enable();
        $this->assertFalse($this->province->disabled);
    }
    
    //
    protected function assertActive()
    {
        $this->province->assertActive();
    }
    public function test_assertActive_disabledProvince_forbidden()
    {
        $this->province->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive province');
    }
    public function test_assertActive_activeProvince_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
}

class TestableProvince extends Province
{

    public string $id;
    public DateTimeImmutable $createdTime;
    public bool $disabled;
    public string $name;
}
