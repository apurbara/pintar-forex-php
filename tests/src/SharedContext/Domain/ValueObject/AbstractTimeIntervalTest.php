<?php

namespace SharedContext\Domain\ValueObject;

use DateInterval;
use DateTimeImmutable;
use Tests\TestBase;

class AbstractTimeIntervalTest extends TestBase
{

    protected $startTime, $endTime;
    protected $time;
    protected $vo, $other;
    protected $twentyFourHoursInterval, $fortyEightHoursInterval;

    protected function setUp(): void
    {
        parent::setUp();
        $this->twentyFourHoursInterval = new DateInterval("PT24H");
        $this->fortyEightHoursInterval = new DateInterval("PT48H");

        $this->startTime = new DateTimeImmutable("-7 days");
        $this->endTime = new DateTimeImmutable("+7 days");
        $this->time = new DateTimeImmutable();

        $this->vo = new TestableTimeIntervalAbstract();
        $this->vo->startTime = $this->startTime;
        $this->vo->endTime = $this->endTime;

        $this->other = new TestableTimeIntervalAbstract();
        $this->other->startTime = $this->startTime->add($this->twentyFourHoursInterval);
        $this->other->endTime = $this->endTime->sub($this->twentyFourHoursInterval);
    }

    protected function executeAfter()
    {
        return $this->vo->after($this->time);
    }

    function test_after_intervalStartTimeAfterArgument_returnTrue()
    {
        $this->time = $this->startTime->sub($this->twentyFourHoursInterval);
        $this->assertTrue($this->executeAfter());
    }

    function test_after_intervalStartTimeBeforeArgument_returnFalse()
    {
        $this->assertFalse($this->executeAfter());
    }

    function test_after_nullStartInterval_returnFalse()
    {
        $this->vo->startTime = null;
        $this->assertFalse($this->executeAfter());
    }

    private function executeBefore()
    {
        return $this->vo->before($this->time);
    }

    function test_before_intervalEndTimeBeforeArgument_returnTrue()
    {
        $this->time = $this->endTime->add($this->twentyFourHoursInterval);
        $this->assertTrue($this->executeBefore());
    }

    function test_before_intervalEndTimeAfterArgument_returnFalse()
    {
        $this->assertFalse($this->executeBefore());
    }

    function test_before_nullEndTimeInterval_returnFalse()
    {
        $this->vo->endTime = null;
        $this->assertFalse($this->executeBefore());
    }

    private function executeContain()
    {
        return $this->vo->contain($this->time);
    }

    function test_contain_argumentInsideInterval_returnTrue()
    {
        $this->assertTrue($this->executeContain());
    }

    function test_contain_argumentBeforeIntervalStartTime_returnFalse()
    {
        $this->time = $this->startTime->sub($this->twentyFourHoursInterval);
        $this->assertFalse($this->executeContain());
    }

    function test_contain_argumentEqualsIntervalStartTime_returnTrue()
    {
        $this->time = $this->startTime;
        $this->assertTrue($this->executeContain());
    }

    function test_contain_nullIntervalStartTime_returnTrue()
    {
        $this->vo->startTime = null;
        $this->assertTrue($this->executeContain());
    }

    function test_contain_argumentAfterIntervalEndTime_returnFalse()
    {
        $this->time = $this->endTime->add($this->twentyFourHoursInterval);
        $this->assertFalse($this->executeContain());
    }

    function test_contain_argumentEqualsIntervalEndTime_returnTrue()
    {
        $this->time = $this->endTime;
        $this->assertTrue($this->executeContain());
    }

    function test_contain_nullIntervalEndTime_returnTrue()
    {
        $this->vo->endTime = null;
        $this->assertTrue($this->executeContain());
    }

    private function executeEncompass()
    {
        return $this->vo->encompass($this->other);
    }

    function test_encompass_otherIntervalInsideInterval()
    {
        $this->assertTrue($this->executeEncompass());
    }

    function test_encompass_otherStartTimeBeforeIntervalStartTime_returnFalse()
    {
        $this->other->startTime = $this->startTime->sub($this->twentyFourHoursInterval);
        $this->assertFalse($this->executeEncompass());
    }

    function test_encommpass_otherStartTimeEqualsIntervalStartTime_returnTrue()
    {
        $this->other->startTime = $this->startTime;
        $this->assertTrue($this->executeEncompass());
    }

    function test_encompass_nullStartTimeInterval_returnTrue()
    {
        $this->vo->startTime = null;
        $this->assertTrue($this->executeEncompass());
    }

    function test_encompass_otherStartTimeIsNull_returnFalse()
    {
        $this->other->startTime = null;
        $this->assertFalse($this->executeEncompass());
    }

    function test_encompass_otherEndTimeAfterIntervalEndTime_returnFalse()
    {
        $this->other->endTime = $this->endTime->add($this->twentyFourHoursInterval);
        $this->assertFalse($this->executeEncompass());
    }

    function test_encompass_otherEndTimeEqualsIntervalEndTime_returnTrue()
    {
        $this->other->endTime = $this->endTime;
        $this->assertTrue($this->executeEncompass());
    }

    function test_encompass_nullEndTimeInterval_returnTrue()
    {
        $this->vo->endTime = null;
        $this->assertTrue($this->executeEncompass());
    }

    function test_encompass_otherEndTimeIntervalIsNull_returnFalse()
    {
        $this->other->endTime = null;
        $this->assertFalse($this->executeEncompass());
    }

    private function executeIntersectWith()
    {
        return $this->vo->intersectWith($this->other);
    }

    function test_intersectWith_containOtherInterval_returnTrue()
    {
        $this->assertTrue($this->executeIntersectWith());
    }

    function test_intersectWith_otherIntervalStartBeforeIntervalAndEndInsideInterval_returnTrue()
    {
        $this->other->startTime = $this->startTime->sub($this->fortyEightHoursInterval);
        $this->assertTrue($this->executeIntersectWith());
    }

    function test_intersectWith_otherIntervalStartWithinIntervalAndEndAfterINterval_returnTrue()
    {
        $this->other->endTime = $this->endTime->add($this->twentyFourHoursInterval);
        $this->assertTrue($this->executeIntersectWith());
    }

    function test_intersectWith_otherIntervalOutsideINterval()
    {
        $this->other->startTime = $this->startTime->sub($this->fortyEightHoursInterval);
        $this->other->endTime = $this->startTime->sub($this->twentyFourHoursInterval);
        $this->assertFalse($this->executeIntersectWith());
    }

    function test_intersectWith_otherFromNullToBeforeInterval_returnFalse()
    {
        $this->other->startTime = null;
        $this->other->endTime = $this->startTime->sub($this->twentyFourHoursInterval);
        $this->assertFalse($this->executeIntersectWith());
    }

    function test_intersectWith_otherStartAfterIntervalEndTimeToNull_returnFalse()
    {
        $this->other->startTime = $this->endTime->add($this->twentyFourHoursInterval);
        $this->other->endTime = null;
        $this->assertFalse($this->executeIntersectWith());
    }

    function test_intersectWith_intervalInsideOther_returnTrue()
    {
        $this->other->startTime = $this->startTime->sub($this->twentyFourHoursInterval);
        $this->other->endTime = $this->endTime->add($this->twentyFourHoursInterval);
        $this->assertTrue($this->executeIntersectWith());
    }

    protected function executeIsUpcoming()
    {
        return $this->vo->isUpcoming();
    }

    public function test_isUpcoming_startTimeInFuture_returnTrue()
    {
        $this->vo->startTime = new DateTimeImmutable("+1 hours");
        $this->assertTrue($this->executeIsUpcoming());
    }

    public function test_isUpcoming_startTimeNotInFuture_returnFalse()
    {
        $this->vo->startTime = new DateTimeImmutable();
        $this->assertFalse($this->executeIsUpcoming());
    }

    protected function executeSameValueAs()
    {
        return $this->vo->sameValueAs($this->other);
    }

    public function test_sameValueAs_sameStarAndEndTime_returnTrue()
    {
        $this->other->startTime = $this->startTime;
        $this->other->endTime = $this->endTime;
        $this->assertTrue($this->executeSameValueAs());
    }

    public function test_sameValueAs_differentStartTime_returnFalse()
    {
        $this->other->endTime = $this->endTime;
        $this->assertFalse($this->executeSameValueAs());
    }

    public function test_sameValueAs_differentEndTime_returnFalse()
    {
        $this->other->startTime = $this->startTime;
        $this->assertFalse($this->executeSameValueAs());
    }
}

class TestableTimeIntervalAbstract extends AbstractTimeInterval
{

    public $startTime, $endTime;

    protected function getStartTimeStamp(): float
    {
        return is_null($this->startTime) ? -INF : $this->startTime->getTimeStamp();
    }

    protected function getEndTimeStamp(): float
    {
        return is_null($this->endTime) ? INF : $this->endTime->getTimeStamp();
    }
}
