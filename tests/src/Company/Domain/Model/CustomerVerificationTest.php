<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\ValueObject\Label;
use Tests\TestBase;

class CustomerVerificationTest extends TestBase
{

    protected $id = 'newId', $position = 2, $weight = 10;

    protected function setUp(): void
    {
        parent::setUp();
    }

    //
    protected function createData()
    {
        return (new CustomerVerificationData($this->createLabelData()))
                        ->setId($this->id)
                        ->setPosition($this->position)
                        ->setWeight($this->weight);
    }

    //
    protected function construct()
    {
        return new TestableCustomerVerification($this->createData());
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
