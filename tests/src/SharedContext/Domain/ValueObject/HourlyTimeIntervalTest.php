<?php

namespace SharedContext\Domain\ValueObject;

use DateTimeImmutable;
use Tests\TestBase;

class HourlyTimeIntervalTest extends TestBase
{
    protected $hourlyTimeInterval;
    protected $startTime = 'next week';

    protected function setUp(): void
    {
        parent::setUp();
        $this->hourlyTimeInterval = new TestableHourlyTimeInterval(new HourlyTimeIntervalData('+1 months'));
    }
    
    //
    protected function createData()
    {
        return new HourlyTimeIntervalData($this->startTime);
    }
    
    //
    protected function construct()
    {
        return new TestableHourlyTimeInterval($this->createData());
    }
    public function test_construct_setProperties()
    {
        $hourlyTimeInterval = $this->construct();
        $startTime = new \DateTimeImmutable($this->startTime);
        $endTime = $startTime->add(new \DateInterval('PT1H'));
        $this->assertEquals($startTime->setTime($startTime->format('H'), 0), $hourlyTimeInterval->startTime);
        $this->assertEquals($endTime->setTime($endTime->format('H'), 0), $hourlyTimeInterval->endTime);
    }
    
}

class TestableHourlyTimeInterval extends HourlyTimeInterval
{
    public ?DateTimeImmutable $startTime;
    public ?DateTimeImmutable $endTime;
}
