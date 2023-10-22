<?php

namespace Tests;

use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use Resources\Exception\RegularException;
use SharedContext\Domain\Model\Form\StringFieldData;
use SharedContext\Domain\Model\FormData;
use SharedContext\Domain\ValueObject\AccountInfoData;
use SharedContext\Domain\ValueObject\BaseFormFieldData;
use SharedContext\Domain\ValueObject\IntegerRangeData;
use SharedContext\Domain\ValueObject\LabelData;

class TestBase extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function assertRegularExceptionThrowed(callable $operation, string $message, string $errorDetail)
    {
        try {
            $operation();
            $this->fail();
        } catch (RegularException $e) {
            $this->assertEquals($message, $e->getMessage());
            $this->assertEquals($errorDetail, $e->getErrorDetail());
        }
    }

    protected function YmdHisStringOfCurrentTime(): string
    {
        return (new DateTime())->format('Y-m-d H:i:s');
    }

    function markAsSuccess()
    {
        $this->assertEquals(1, 1);
    }

    protected function buildMockOfClass(string $className)
    {
        return $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
    }

    protected function buildMockOfInterface(string $className)
    {
        return $this->getMockBuilder($className)->getMock();
    }
    
    protected function buildMockOfReadonlyClass(string $className)
    {
        $classReflection = new ReflectionClass($className);
        return $classReflection->newInstanceWithoutConstructor();
    }
    
    protected function createInspectableObject(object $object): ReflectionObject
    {
        return new \ReflectionObject($object);
    }

//    protected function currentTimeWithSecAccuracy(): DateTimeImmutable
//    {
//        return DateTimeImmutableBuilder::buildYmdHisAccuracy();
//    }
//    protected function assertDateTimeImmutableValueEqualsNow($value)
//    {
//        return DateTimeImmutableBuilder::buildYmdHisAccuracy() == $value;
//    }

    protected function assertDateTimeImmutableYmdHisValueEquals(DateTimeImmutable $value, DateTimeImmutable $expected)
    {
        $this->assertEquals($expected->format('Y-m-d H:i:s'), $value->format('Y-m-d H:i:s'));
    }

    protected function assertDateTimeImmutableYmdHisValueEqualsNow(DateTimeImmutable $value)
    {
        return $this->assertDateTimeImmutableYmdHisValueEquals($value, new DateTimeImmutable());
    }
    
    protected function assertObjectPropertyValueEquals(object $object, string $propertyName, mixed $value)
    {
        $objectReflection = new \ReflectionObject($object);
        $propertyReflection = $objectReflection->getProperty($propertyName);
        $this->assertEquals($propertyReflection->getValue($object), $value);
    }

    //
    protected function getObjectPropertyValue($object, string $propertyName): mixed
    {
        return (new ReflectionProperty($object, $propertyName))->getValue($object);
    }

    protected function setObjectPropertyValue($object, string $propertyName, $value): void
    {
        (new ReflectionProperty($object, $propertyName))->setValue($object, $value);
    }

    protected function assertPropertyEquals($expected, $object, $propertyName)
    {
        $this->assertEquals($expected, $this->getObjectPropertyValue($object, $propertyName));
    }

    protected function assertPropertySame($expected, $object, $propertyName)
    {
        $this->assertSame($expected, $this->getObjectPropertyValue($object, $propertyName));
    }

    //
    protected function createAccountInfoData()
    {
        return new AccountInfoData('name', 'user@email.org', 'password123', null);
    }

    protected function createLabelData()
    {
        return new LabelData('label name', 'label description');
    }

    protected function createFormData()
    {
        $formData = new FormData();
        $baseFormFieldData = new BaseFormFieldData('name', null, null, false);
        $minMaxlengthData = new IntegerRangeData(null, null);
        $stringFieldData = new StringFieldData($baseFormFieldData, $minMaxlengthData, null);
        $formData->addStringFieldData($stringFieldData, null);
        return $formData;
    }
}
