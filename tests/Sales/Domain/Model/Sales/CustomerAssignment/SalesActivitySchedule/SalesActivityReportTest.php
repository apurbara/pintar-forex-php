<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;

use DateTimeImmutable;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Tests\TestBase;

class SalesActivityReportTest extends TestBase
{

    protected $salesActivitySchedule;
    //
    protected $id = 'newId', $content = 'new content';

    protected function setUp(): void
    {
        parent::setUp();
        $this->salesActivitySchedule = $this->buildMockOfClass(SalesActivitySchedule::class);
    }

    //
    protected function createData()
    {
        return (new SalesActivityReportData($this->content));
    }

    //
    protected function construct()
    {
        return new TestableSalesActivityReport($this->salesActivitySchedule, $this->id, $this->createData());
    }
    public function test_construct_setProperties()
    {
        $report = $this->construct();
        $this->assertSame($this->salesActivitySchedule, $report->salesActivitySchedule);
        $this->assertSame($this->id, $report->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($report->submitTime);
        $this->assertSame($this->content, $report->content);
    }
    public function test_construct_assertScheduleIncomplete()
    {
        $this->salesActivitySchedule->expects($this->once())
                ->method('assertIncomplete');
        $this->construct();
    }
    public function test_construct_setScheduleCompleted()
    {
        $this->salesActivitySchedule->expects($this->once())
                ->method('markAsCompleted');
        $this->construct();
    }
}

class TestableSalesActivityReport extends SalesActivityReport
{

    public SalesActivitySchedule $salesActivitySchedule;
    public string $id;
    public DateTimeImmutable $submitTime;
    public string $content;
}
