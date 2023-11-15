<?php

namespace Sales\Domain\Service;

use DateTimeImmutable;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\SalesActivity;
use Tests\TestBase;

class SalesActivitySchedulerServiceTest extends TestBase
{

    protected $scheduler;
    //
    protected $startTime, $schedule, $scheduleOne, $scheduleTwo;
    protected $salesActivity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduler = new TestableSalesActivitySchedulerService();
        //
        $this->startTime = (new DateTimeImmutable('tomorrow'))->setTime(10, 0);
        
        $this->schedule = $this->buildMockOfClass(SalesActivitySchedule::class);
        $this->scheduleOne = $this->buildMockOfClass(SalesActivitySchedule::class);
        $this->scheduleTwo = $this->buildMockOfClass(SalesActivitySchedule::class);
        
        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
    }

    //
    protected function add()
    {
        $this->scheduler->add($this->startTime, $this->scheduleOne);
    }

    public function test_add_appendScheduleToList()
    {
        $this->add();
        $this->assertEquals($this->scheduleOne, $this->scheduler->scheduleList[$this->startTime->format('Y-m-d H')][0]);
    }

    //
    protected function nextAvailableStartTimeForInitialSalesActivity()
    {
        $this->salesActivity->expects($this->any())
                ->method('getDuration')
                ->willReturn(3);
        $this->scheduleOne->expects($this->any())
                ->method('getActivityDuration')
                ->willReturn(30);
        $this->scheduleTwo->expects($this->any())
                ->method('getActivityDuration')
                ->willReturn(3);
        
        $this->scheduler->add($this->startTime, $this->scheduleOne);
        $this->scheduler->add($this->startTime, $this->scheduleTwo);
        return $this->scheduler->nextAvailableStartTimeForInitialSalesActivity($this->salesActivity);
    }

    // manually modify system time for this test
    public function test_nextAvailableStartTimeForInitialSalesActivity_returnNextDayAtTen()
    {
        //if now is saturday, set schedule +2 days to next monday
        if ((new \DateTimeImmutable())->format('w') == 6) {
            $this->assertEquals((new \DateTimeImmutable('+2 days'))->setTime(10, 0),
                    $this->nextAvailableStartTimeForInitialSalesActivity());
        } elseif ((new \DateTimeImmutable ())->format('w') == 5) {// if friday set +3 days to monday
            $this->assertEquals((new \DateTimeImmutable('+3 days'))->setTime(10, 0),
                    $this->nextAvailableStartTimeForInitialSalesActivity());
        } else { // otherwise, set schedule for next day
            $this->assertEquals((new \DateTimeImmutable('+1 days'))->setTime(10, 0),
                    $this->nextAvailableStartTimeForInitialSalesActivity());
        }
    }
    // manually modify system time for this test
    public function test_nextAvailableStartTimeForInitialSalesActivity_onFriday_returnNextMondayTen()
    {
        if ((new \DateTimeImmutable())->format('w') == 5) {
            $this->assertEquals((new \DateTimeImmutable('+3 days'))->setTime(10, 0),
                    $this->nextAvailableStartTimeForInitialSalesActivity());
        } 
    }
    // manually modify system time for this test
    public function test_nextAvailableStartTimeForInitialSalesActivity_onSaturday_returnNextMondayTen()
    {
        if ((new \DateTimeImmutable())->format('w') == 6) {
            $this->assertEquals((new \DateTimeImmutable('+2 days'))->setTime(10, 0),
                    $this->nextAvailableStartTimeForInitialSalesActivity());
        }
    }
    public function test_nextAvailableStartTimeForInitialSalesActivity_noAvailableSlot_tryNextHour()
    {
        $this->scheduleOne->expects($this->once())
                ->method('getActivityDuration')
                ->willReturn(57);
        $currentDayOfWeek = (new \DateTimeImmutable())->format('w');
        if ( $currentDayOfWeek != 5 && $currentDayOfWeek != 6) {
            $this->assertEquals((new \DateTimeImmutable('+1 days'))->setTime(11, 0),
                    $this->nextAvailableStartTimeForInitialSalesActivity());
        }
    }
    public function test_nextAvailableStartTimeForInitialSalesActivity_noAvailableSlotForNextDayBetweenTenToFourteen_tryDayAfterTomorrow()
    {
        $this->schedule->expects($this->any())
                ->method('getActivityDuration')
                ->willReturn(60);
        $tomorrow = new DateTimeImmutable('tomorrow');
        $this->scheduler->add($tomorrow->setTime(10, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(11, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(12, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(13, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(14, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(15, 0), $this->schedule);
        
        $currentDayOfWeek = (new \DateTimeImmutable())->format('w');
        if ( $currentDayOfWeek != 4 && $currentDayOfWeek != 5) {
            $this->assertEquals((new \DateTimeImmutable('+2 days'))->setTime(10, 0),
                    $this->nextAvailableStartTimeForInitialSalesActivity());
        }
    }
    public function test_nextAvailableStartTimeForInitialSalesActivity_noAvailableSlotForNextDayBetweenTenToFourteenOnSaturday_tryNextMonday()
    {
        $this->schedule->expects($this->any())
                ->method('getActivityDuration')
                ->willReturn(60);
        $tomorrow = new DateTimeImmutable('tomorrow');
        $this->scheduler->add($tomorrow->setTime(10, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(11, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(12, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(13, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(14, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(15, 0), $this->schedule);
        
        $currentDayOfWeek = (new \DateTimeImmutable())->format('w');
        if ( $currentDayOfWeek == 4) {
            $this->assertEquals((new \DateTimeImmutable('+4 days'))->setTime(10, 0),
                    $this->nextAvailableStartTimeForInitialSalesActivity());
        }
    }
}

class TestableSalesActivitySchedulerService extends SalesActivitySchedulerService
{

    public array $scheduleList;
}
