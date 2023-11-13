<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\ValueObject\Label;
use SharedContext\Domain\ValueObject\LabelData;
use Tests\TestBase;

class CustomerJourneyTest extends TestBase
{
    protected  $customerJourney;
    protected $id = 'newId', $initial = true;
    
    protected function setUp(): void
    {
        parent::setUp();
        $data = (new CustomerJourneyData(new LabelData('name', 'description')))
                ->setId('id');
        $this->customerJourney = new TestableCustomerJourney('id', $data);
    }
    
    //
    protected function createData()
    {
        return (new CustomerJourneyData($this->createLabelData()));
    }
    
    //
    protected function construct()
    {
        return new TestableCustomerJourney($this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $customerJourney = $this->construct();
        $this->assertSame($this->id, $customerJourney->id);
        $this->assertFalse($customerJourney->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($customerJourney->createdTime);
        $this->assertInstanceOf(Label::class, $customerJourney->label);
        $this->assertFalse($customerJourney->initial);
    }
    public function test_construct_initialSet()
    {
        $payload = $this->createData()->setInitial();
        $customerJourney = new TestableCustomerJourney($this->id, $payload);
        $this->assertTrue($customerJourney->initial);
    }
    
    //
    protected function update()
    {
        $this->customerJourney->update($this->createData());
    }
    public function test_update_updateProperties()
    {
        $this->update();
        $this->assertEquals(new Label($this->createLabelData()), $this->customerJourney->label);
    }
}

class TestableCustomerJourney extends CustomerJourney
{
    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public Label $label;
    public bool $initial;
}
