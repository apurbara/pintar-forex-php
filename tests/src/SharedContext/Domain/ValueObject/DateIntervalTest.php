<?php

namespace SharedContext\Domain\ValueObject;

use DateTimeImmutable;
use Tests\TestBase;

class DateIntervalTest extends TestBase
{

    protected $vo;
    protected $startDate = '-1 months', $endDate = '+1 months';

    protected function setUp(): void
    {
        parent::setUp();
        $this->vo = new TestableDateInterval(new DateIntervalData('-7 days', '+7 days'));
    }
    
    //
    protected function buildDateIntervalData()
    {
        return new DateIntervalData($this->startDate, $this->endDate);
    }

    //
    function test_getStartTimeStamp_returnTimestamptOfStartTime()
    {
        $startOfDay = $this->vo->startDate->setTime(0, 0)->getTimestamp();
        $this->assertEquals($startOfDay, floatval($this->vo->getStartTimeStamp()));
    }

    function test_getStartTimeStamp_nullStartDate_returnMinusInfinite()
    {
        $this->vo->startDate = null;
        $this->assertSame(-INF, $this->vo->getStartTimeStamp());
    }

    function test_getEndTimeStamp_returnTimestamptOfEndTimeEndOfDay()
    {
        $endOfDay = $this->vo->endDate->setTime(23, 59, 59)->getTimestamp();
        $this->assertEquals($endOfDay, $this->vo->getEndTimeStamp());
    }

    function test_getEndTimeStamp_nullEndDate_returnMinusInfinite()
    {
        $this->vo->endDate = null;
        $this->assertSame(INF, $this->vo->getEndTimeStamp());
    }

    //
    protected function executeConstruct()
    {
        return new TestableDateInterval($this->buildDateIntervalData());
    }

    public function test_construct_setPropertiesNormalizeToDateParameters()
    {
        $vo = $this->executeConstruct();
        
        $startDate = (new DateTimeImmutable($this->startDate))->setTime(0, 0);
        $endDate = (new DateTimeImmutable($this->endDate))->setTime(0, 0);
        $this->assertEquals($startDate, $vo->startDate);
        $this->assertEquals($endDate, $vo->endDate);
    }

    public function test_construct_emptyStartDate_setStartDateNull()
    {
        $this->startDate = null;
        $vo = $this->executeConstruct();
        $this->assertNull($vo->startDate);
    }

    public function test_construct_emptyEndDate_setEndDateNull()
    {
        $this->endDate = null;
        $vo = $this->executeConstruct();
        $this->assertNull($vo->endDate);
    }

    public function test_construct_endDateLessThanStartDate_badRequest()
    {
        $this->endDate = '-2 months';
        $operation = function () {
            $this->executeConstruct();
        };
        $this->assertRegularExceptionThrowed($operation, 'Bad Request',
                'bad request: end date must be bigger than or equals start date');
    }
}

class TestableDateInterval extends DateInterval
{

    public ?DateTimeImmutable $startDate;
    public ?DateTimeImmutable $endDate;

    public function getStartTimeStamp(): float
    {
        return parent::getStartTimeStamp();
    }

    public function getEndTimeStamp(): float
    {
        return parent::getEndTimeStamp();
    }
}
