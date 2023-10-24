<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\ValueObject\Label;
use SharedContext\Domain\ValueObject\LabelData;
use Tests\TestBase;

class SalesActivityTest extends TestBase
{
    protected  $salesActivity;
    protected $id = 'newId', $duration = 15, $initial = true;
    
    protected function setUp(): void
    {
        parent::setUp();
        $data = (new SalesActivityData(new LabelData('name', 'description'), 30))->setId('id');
        $this->salesActivity = new TestableSalesActivity($data);
    }
    
    //
    protected function createData()
    {
        return (new SalesActivityData($this->createLabelData(), $this->duration))
                ->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableSalesActivity($this->createData(), $this->initial);
    }
    public function test_construct_setProperties()
    {
        $salesActivity = $this->construct();
        $this->assertSame($this->id, $salesActivity->id);
        $this->assertFalse($salesActivity->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($salesActivity->createdTime);
        $this->assertInstanceOf(Label::class, $salesActivity->label);
        $this->assertSame($this->duration, $salesActivity->duration);
        $this->assertSame($this->initial, $salesActivity->initial);
    }
    public function test_construct_durationExceedSixty_badRequest()
    {
        $this->duration = 61;
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'duration is mandatory and must not exceed 60');
    }
    public function test_construct_emptyDuration_badRequest()
    {
        $this->duration = 0;
        $this->assertRegularExceptionThrowed(fn() => $this->construct(), 'Bad Request', 'duration is mandatory and must not exceed 60');
    }
    
    //
    protected function update()
    {
        $this->salesActivity->update($this->createData());
    }
    public function test_update_updateProperties()
    {
        $this->update();
        $this->assertEquals(new Label($this->createLabelData()), $this->salesActivity->label);
        $this->assertEquals($this->duration, $this->salesActivity->duration);
    }
    public function test_update_emptyDuration_badRequest()
    {
        $this->duration = 0;
        $this->assertRegularExceptionThrowed(fn() => $this->update(), 'Bad Request', 'duration is mandatory and must not exceed 60');
    }
}

class TestableSalesActivity extends SalesActivity
{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public Label $label;
    public int $duration;
    public bool $initial;
}
