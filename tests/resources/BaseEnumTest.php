<?php

namespace Resources;

use Tests\TestBase;

class BaseEnumTest extends TestBase
{

    protected $value = 1;
    protected $enum;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enum = new TestableBaseEnum($this->value);
    }

    protected function executeConstruct()
    {
        return new TestableBaseEnum($this->value);
    }

    public function test_construct_setProperties()
    {
        $baseEnum = $this->executeConstruct();
        $this->assertEquals($this->value, $baseEnum->value);
    }

    public function test_construct_invalidValue_badRequest()
    {
        $this->value = 111;
        $this->assertRegularExceptionThrowed(function () {
            $this->executeConstruct();
        }, 'Bad Request', 'bad request: invalid TestableBaseEnum argument');
    }

    protected function getDisplayValue()
    {
        return $this->enum->getDisplayValue();
    }

    public function test_getDisplayValue_returnCorrespondingConstValue()
    {
        $this->assertSame('VALID_VALUE_ONE', $this->getDisplayValue());
    }

    //
    protected function getValueDefinition()
    {
        return TestableBaseEnum::getValueDefinition();
    }

    public function test_getValueDefinition_returnContantsDefinition()
    {
        $this->assertEquals([
            'VALID_VALUE_ONE' => TestableBaseEnum::VALID_VALUE_ONE,
            'VALID_VALUE_TWO' => TestableBaseEnum::VALID_VALUE_TWO,
                ], $this->getValueDefinition());
    }
}

class TestableBaseEnum extends BaseEnum
{

    const VALID_VALUE_ONE = 1;
    const VALID_VALUE_TWO = 2;

    public $value;
}
