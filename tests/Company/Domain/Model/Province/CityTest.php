<?php

namespace Company\Domain\Model\Province;

use Company\Domain\Model\Province;
use DateTimeImmutable;
use Tests\TestBase;

class CityTest extends TestBase
{

    protected $province;
    protected $city;
    protected $id = 'newId', $name = 'new city name';

    protected function setUp(): void
    {
        parent::setUp();
        $this->province = $this->buildMockOfClass(Province::class);

        $data = (new CityData())->setName('city');
        $this->city = new TestableCity($this->province, 'id', $data);
    }

    //
    protected function buildData()
    {
        return (new CityData())
                        ->setName($this->name);
    }
    
    //
    protected function construct()
    {
        return new TestableCity($this->province, $this->id, $this->buildData());
    }
    public function test_construct_setProperties()
    {
        $city = $this->construct();
        $this->assertSame($this->province, $city->province);
        $this->assertSame($this->id, $city->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($city->createdTime);
        $this->assertFalse($city->disabled);
        $this->assertSame($this->name, $city->name);
    }
    public function test_construct_emptyName_BadRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'city name is mandatory');
    }
    public function test_construct_assertProvinceActive()
    {
        $this->province->expects($this->once())
                ->method('assertActive');
        $this->construct();
    }
    
    //
    protected function update()
    {
        $this->city->update($this->province, $this->buildData());
    }
    public function test_update_updateProperties()
    {
        $this->city->province = $this->buildMockOfClass(Province::class);
        $this->update();
        $this->assertSame($this->province, $this->city->province);
        $this->assertSame($this->name, $this->city->name);
    }
    public function test_update_emptyName_BadRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'city name is mandatory');
    }
    public function test_update_assertProvinceActive()
    {
        $this->province->expects($this->once())
                ->method('assertActive');
        $this->update();
    }
    
    //
    protected function disable()
    {
        $this->city->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->city->disabled);
    }
    
    //
    protected function enable()
    {
        $this->city->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->city->disabled = true;
        $this->enable();
        $this->assertFalse($this->city->disabled);
    }
    
    //
    protected function assertActive()
    {
        $this->city->assertActive();
    }
    public function test_assertActive_disabledCity_forbidden()
    {
        $this->city->disabled = true;
        $this->assertRegularExceptionThrowed(fn() => $this->assertActive(), 'Forbidden', 'inactive city');
    }
    public function test_assertActive_activeCity_void()
    {
        $this->assertActive();
        $this->markAsSuccess();
    }
}

class TestableCity extends City
{

    public Province $province;
    public string $id;
    public DateTimeImmutable $createdTime;
    public bool $disabled;
    public string $name;
}
