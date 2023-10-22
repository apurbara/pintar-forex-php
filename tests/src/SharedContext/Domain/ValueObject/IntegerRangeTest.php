<?php

namespace SharedContext\Domain\ValueObject;

use Tests\TestBase;

class IntegerRangeTest extends TestBase
{

    protected $vo;
    protected $otherVo;
    protected $value = 9;
    protected $minimumValue = 111, $maximumValue = 9999;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vo = new TestableIntegerRange(new IntegerRangeData(1, 99));
        $this->otherVo = new TestableIntegerRange(new IntegerRangeData(1, 99));
    }
    
    //
    protected function buildIntegerRangeData()
    {
        return new IntegerRangeData($this->minimumValue, $this->maximumValue);
    }

    //
    private function executeConstruct()
    {
        return new TestableIntegerRange($this->buildIntegerRangeData());
    }

    public function test_construct_setProperties()
    {
        $vo = $this->executeConstruct();
        $this->assertEquals($this->minimumValue, $vo->minimumValue);
        $this->assertEquals($this->maximumValue, $vo->maximumValue);
    }

    public function test_construct_maxValueLessThanMinValue_forbidden()
    {
        $this->minimumValue = $this->maximumValue + 1;
        $operation = function () {
            $this->executeConstruct();
        };
        $this->assertRegularExceptionThrowed($operation, 'Forbidden',
                'forbidden: max value must be bigger than min value');
    }

    public function test_construct_nullMaxValue_constructNormally()
    {
        $this->maximumValue = null;
        $this->executeConstruct();
        $this->markAsSuccess();
    }

    public function test_construct_nonIntegerMinimumValue_expectedResult()
    {
        $this->minimumValue = 'non integer';
        $this->expectException(\TypeError::class);
        $this->executeConstruct();
    }

    public function test_construct_nonIntegerMaximumValue_expectedResult()
    {
        $this->maximumValue = 'non integer';
        $this->expectException(\TypeError::class);
        $this->executeConstruct();
    }

    //
    protected function executeSameValueAs()
    {
        return $this->vo->sameValueAs($this->otherVo);
    }

    function test_sameValueAs_sameVo_returnTrue()
    {
        $this->assertTrue($this->executeSameValueAs());
    }

    function test_sameValueAs_differentMinValue_returnFalse()
    {
        $this->otherVo->minimumValue = null;
        $this->assertFalse($this->executeSameValueAs());
    }

    function test_sameValueAs_differentMaxValue_returnFalse()
    {
        $this->otherVo->maximumValue = 7;
        $this->assertFalse($this->executeSameValueAs());
    }

    //
    protected function executeContain()
    {
        return $this->vo->contain($this->value);
    }

    public function test_contain_valueInRange_returnTrue()
    {
        $this->assertTrue($this->executeContain());
    }

    public function test_contain_valueLessThanMinValue_returnFalse()
    {
        $this->value = $this->minimumValue - 1;
        $this->assertFalse($this->executeContain());
    }

    public function test_contain_valueEqualsMinValue_returnTrue()
    {
        $this->value = $this->vo->minimumValue;
        $this->assertTrue($this->executeContain());
    }

    public function test_contain_valueBiggerThanMaxValue_returnFalse()
    {
        $this->value = $this->vo->maximumValue + 1;
        $this->assertFalse($this->executeContain());
    }

    public function test_contain_valueEqualsMaxValue_returnTrue()
    {
        $this->value = $this->vo->maximumValue;
        $this->assertTrue($this->executeContain());
    }

    public function test_contain_nullMaxValue_returnTrue()
    {
        $this->vo->maximumValue = null;
        $this->assertTrue($this->executeContain());
    }
}

class TestableIntegerRange extends IntegerRange
{

    public ?int $minimumValue = null;
    public ?int $maximumValue = null;
}
