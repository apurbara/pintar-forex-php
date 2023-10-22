<?php

namespace SharedContext\Domain\ValueObject;

use DateInterval;
use DateTimeImmutable;
use Tests\TestBase;

class TimeIntervalTest extends TestBase
{

    protected $vo;
    protected $startTime, $endTime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vo = new TestableTimeInterval(new TimeIntervalData('+128 hours', '+256 hours'));
        $this->startTime = (new \DateTime('+24 hours'))->format('Y-m-d H:i:s');
        $this->endTime = (new \DateTime('+48 hours'))->format('Y-m-d H:i:s');
    }

    //
    protected function buildTimeIntervalData()
    {
        return new TimeIntervalData($this->startTime, $this->endTime);
    }
    //
    public function test_getStartTimeStamp_returnTimestampOfStartTime()
    {
        $this->assertEquals($this->vo->startTime->getTimestamp(), $this->vo->getStartTimeStamp());
    }

    public function test_getStartTimeStamp_nullStartTime_returnMinusINF()
    {
        $this->vo->startTime = null;
        $this->assertSame(-INF, $this->vo->getStartTimeStamp());
    }

    //
    public function test_getEndTimeStamp_returnTimestampOfEndTime()
    {
        $this->assertEquals($this->vo->endTime->getTimestamp(), $this->vo->getEndTimeStamp());
    }

    public function test_getEndTimeStamp_nullEndTime_returnMinusINF()
    {
        $this->vo->endTime = null;
        $this->assertSame(INF, $this->vo->getEndTimeStamp());
    }

    //
    private function executeConstruct()
    {
        return new TestableTimeInterval($this->buildTimeIntervalData());
    }

    public function test_construct_setProperties()
    {
        $vo = $this->executeConstruct();
        $startTime = new DateTimeImmutable($this->startTime);
        $endTime = new DateTimeImmutable($this->endTime);
        $this->assertEquals($startTime, $vo->startTime);
        $this->assertEquals($endTime, $vo->endTime);
    }

    public function test_construct_endTimeLessThanStartTime_badRequest()
    {
        $this->startTime = '+49 hours';
        $operation = function () {
            $this->executeConstruct();
        };
        $this->assertRegularExceptionThrowed($operation, 'Bad Request',
                'bad request: end time must be bigger than start time');
    }

    public function test_construct_endTimeEqualsStartTime_badRequest()
    {
        $this->endTime = $this->startTime;
        $operation = function () {
            $this->executeConstruct();
        };
        $this->assertRegularExceptionThrowed($operation, 'Bad Request',
                'bad request: end time must be bigger than start time');
    }
}

class TestableTimeInterval extends TimeInterval
{

    public ?DateTimeImmutable $startTime;
    public ?DateTimeImmutable $endTime;

    public function getStartTimeStamp(): float
    {
        return parent::getStartTimeStamp();
    }

    public function getEndTimeStamp(): float
    {
        return parent::getEndTimeStamp();
    }
}
