<?php

namespace Company\Domain\Model\Personnel;

use Company\Domain\Model\Personnel;
use DateTimeImmutable;
use Tests\TestBase;

class ManagerTest extends TestBase
{

    protected $personnel;
    protected $manager;
    //
    protected $id = 'newId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->personnel = $this->buildMockOfClass(Personnel::class);
        $this->manager = new TestableManager($this->personnel, (new ManagerData())->setId('id'));
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
    protected function disable()
    {
        $this->manager->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->manager->disabled);
    }
    
    //
    protected function enable()
    {
        $this->manager->enable();
    }
    public function test_enable_setDisabledFalse()
    {
        $this->manager->disabled = true;
        $this->enable();
        $this->assertFalse($this->manager->disabled);
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
}

class TestableManager extends Manager
{

    public Personnel $personnel;
    public string $id;
    public DateTimeImmutable $createdTime;
    public bool $disabled;
}
