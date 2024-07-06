<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use Shared\Domain\ValueObject\Label;
use Tests\TestBase;

class CustomerVerificationTest extends TestBase
{
    protected $customerVerification;
    protected $id = 'newId', $position = 2, $weight = 10;

    protected function setUp(): void
    {
        parent::setUp();
        $data = (new CustomerVerificationData($this->createLabelData()))
                ->setPosition(99)
                ->setWeight(37);
        $this->customerVerification = new TestableCustomerVerification('id', $data);
        $this->customerVerification->label = $this->buildMockOfClass(Label::class);
    }

    //
    protected function createData()
    {
        return (new CustomerVerificationData($this->createLabelData()))
                        ->setPosition($this->position)
                        ->setWeight($this->weight);
    }

    //
    protected function construct()
    {
        return new TestableCustomerVerification($this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $customerVerification = $this->construct();
        $this->assertSame($this->id, $customerVerification->id);
        $this->assertfalse($customerVerification->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($customerVerification->createdTime);
        $this->assertInstanceOf(Label::class, $customerVerification->label);
        $this->assertSame($this->weight, $customerVerification->weight);
        $this->assertSame($this->position, $customerVerification->position);
    }
    
    //
    protected function update()
    {
        $this->customerVerification->update($this->createData());
    }
    public function test_update_updateProperties()
    {
        $this->update();
        $this->assertEquals(new Label($this->createLabelData()), $this->customerVerification->label);
        $this->assertSame($this->weight, $this->customerVerification->weight);
        $this->assertSame($this->position, $this->customerVerification->position);
    }
    
    //
    protected function disable()
    {
        $this->customerVerification->disable();
    }
    public function test_disable_setDisabled()
    {
        $this->disable();
        $this->assertTrue($this->customerVerification->disabled);
    }
    
    //
    protected function enable()
    {
        $this->customerVerification->enable();
    }
    public function test_enable_setEnabled()
    {
        $this->customerVerification->disabled = true;
        $this->enable();
        $this->assertFalse($this->customerVerification->disabled);
    }
}

class TestableCustomerVerification extends CustomerVerification
{

    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public Label $label;
    public int $weight;
    public int $position;
}
