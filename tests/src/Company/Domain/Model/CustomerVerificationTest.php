<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\ValueObject\Label;
use Tests\TestBase;

class CustomerVerificationTest extends TestBase
{

    protected $id = 'newId';

    protected function setUp(): void
    {
        parent::setUp();
    }

    //
    protected function createData()
    {
        return (new CustomerVerificationData($this->createLabelData()))
                        ->setId($this->id);
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
    }
}

class TestableCustomerVerification extends CustomerVerification
{

    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public Label $label;
}
