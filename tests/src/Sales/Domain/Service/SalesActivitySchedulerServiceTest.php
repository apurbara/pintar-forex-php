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
    protected $tomorrow, $tomorrowAtTen, $schedule, $scheduleOne, $scheduleTwo, $scheduleThree;
//    protected $salesActivity;
    protected $requiredDuration = 20;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduler = new TestableSalesActivitySchedulerService();
        //
        $this->tomorrow = new DateTimeImmutable('tomorrow');
        $this->tomorrowAtTen = (new DateTimeImmutable('tomorrow'))->setTime(10, 0);
        
        $this->schedule = $this->buildMockOfClass(SalesActivitySchedule::class);
        $this->scheduleOne = $this->buildMockOfClass(SalesActivitySchedule::class);
        $this->scheduleTwo = $this->buildMockOfClass(SalesActivitySchedule::class);
        $this->scheduleThree = $this->buildMockOfClass(SalesActivitySchedule::class);
        
//        $this->salesActivity = $this->buildMockOfClass(SalesActivity::class);
//        $this->salesActivity->expects($this->any())->method('getDuration')->willReturn(3);
    }

    //
    protected function add()
    {
        $this->scheduleOne->expects($this->any())->method('getActivityDuration')->willReturn(30);
        $this->scheduler->add($this->tomorrowAtTen, $this->scheduleOne);
    }
    public function test_add_appendScheduleToListAndSetOccupiedList()
    {
        $this->add();
        $this->assertTrue($this->scheduler->scheduleList[$this->tomorrowAtTen->format('Y-m-d H')]->contains($this->scheduleOne));
        $this->assertEquals(30, $this->scheduler->occupiedDurationList[$this->tomorrowAtTen->format('Y-m-d H')]);
    }
    public function test_add_aggregateOccupiedDuration()
    {
        $this->scheduler->occupiedDurationList[$this->tomorrowAtTen->format('Y-m-d H')] = 20;
        $this->add();
        $this->assertEquals(50, $this->scheduler->occupiedDurationList[$this->tomorrowAtTen->format('Y-m-d H')]);
    }

    //
//    protected function nextAvailableStartTimeForInitialSalesActivity()
//    {
//        $this->scheduler->add($this->tomorrowAtTen, $this->scheduleOne);
//        $this->scheduler->add($this->tomorrowAtTen, $this->scheduleTwo);
//        return $this->scheduler->nextAvailableStartTimeForInitialSalesActivity($this->salesActivity);
//    }
//    // manually modify system time for this test
//    public function test_nextAvailableStartTimeForInitialSalesActivity_returnNextDayAtTen()
//    {
//        //if now is saturday, set schedule +2 days to next monday
//        if ((new \DateTimeImmutable())->format('w') == 6) {
//            $this->assertEquals((new \DateTimeImmutable('+2 days'))->setTime(10, 0),
//                    $this->nextAvailableStartTimeForInitialSalesActivity());
//        } elseif ((new \DateTimeImmutable ())->format('w') == 5) {// if friday set +3 days to monday
//            $this->assertEquals((new \DateTimeImmutable('+3 days'))->setTime(10, 0),
//                    $this->nextAvailableStartTimeForInitialSalesActivity());
//        } else { // otherwise, set schedule for next day
//            $this->assertEquals((new \DateTimeImmutable('+1 days'))->setTime(10, 0),
//                    $this->nextAvailableStartTimeForInitialSalesActivity());
//        }
//    }
//    // manually modify system time for this test
//    public function test_nextAvailableStartTimeForInitialSalesActivity_onFriday_returnNextMondayTen()
//    {
//        if ((new \DateTimeImmutable())->format('w') == 5) {
//            $this->assertEquals((new \DateTimeImmutable('+3 days'))->setTime(10, 0),
//                    $this->nextAvailableStartTimeForInitialSalesActivity());
//        } 
//    }
//    // manually modify system time for this test
//    public function test_nextAvailableStartTimeForInitialSalesActivity_onSaturday_returnNextMondayTen()
//    {
//        if ((new \DateTimeImmutable())->format('w') == 6) {
//            $this->assertEquals((new \DateTimeImmutable('+2 days'))->setTime(10, 0),
//                    $this->nextAvailableStartTimeForInitialSalesActivity());
//        }
//    }
//    public function test_nextAvailableStartTimeForInitialSalesActivity_noAvailableSlot_tryNextHour()
//    {
//        $this->scheduleOne->expects($this->any())
//                ->method('getActivityDuration')
//                ->willReturn(57);
//        $currentDayOfWeek = (new \DateTimeImmutable())->format('w');
//        if ( $currentDayOfWeek != 5 && $currentDayOfWeek != 6) {
//            $this->assertEquals((new \DateTimeImmutable('+1 days'))->setTime(11, 0),
//                    $this->nextAvailableStartTimeForInitialSalesActivity());
//        }
//    }
//    public function test_nextAvailableStartTimeForInitialSalesActivity_noAvailableSlotForNextDayBetweenTenToFourteen_tryDayAfterTomorrow()
//    {
//        $this->schedule->expects($this->any())
//                ->method('getActivityDuration')
//                ->willReturn(60);
//        $tomorrow = new DateTimeImmutable('tomorrow');
//        $this->scheduler->add($tomorrow->setTime(10, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(11, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(12, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(13, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(14, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(15, 0), $this->schedule);
//        
//        $currentDayOfWeek = (new \DateTimeImmutable())->format('w');
//        if ( $currentDayOfWeek != 4 && $currentDayOfWeek != 5) {
//            $this->assertEquals((new \DateTimeImmutable('+2 days'))->setTime(10, 0),
//                    $this->nextAvailableStartTimeForInitialSalesActivity());
//        }
//    }
//    public function test_nextAvailableStartTimeForInitialSalesActivity_noAvailableSlotForNextDayBetweenTenToFourteenOnSaturday_tryNextMonday()
//    {
//        $this->schedule->expects($this->any())
//                ->method('getActivityDuration')
//                ->willReturn(60);
//        $tomorrow = new DateTimeImmutable('tomorrow');
//        $this->scheduler->add($tomorrow->setTime(10, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(11, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(12, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(13, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(14, 0), $this->schedule);
//        $this->scheduler->add($tomorrow->setTime(15, 0), $this->schedule);
//        
//        $currentDayOfWeek = (new \DateTimeImmutable())->format('w');
//        if ( $currentDayOfWeek == 4) {
//            $this->assertEquals((new \DateTimeImmutable('+4 days'))->setTime(10, 0),
//                    $this->nextAvailableStartTimeForInitialSalesActivity());
//        }
//    }
    
    //
    protected function nextAvailableTimeSlotForScheduleWithDuration()
    {
        $this->schedule->expects($this->any())->method('getActivityDuration')->willReturn(60);
        return $this->scheduler->nextAvailableTimeSlotForScheduleWithDuration($this->requiredDuration);
    }
    public function test_nextAvailableTimeSlotForScheduleWithDuration_returnNextAvailableDayAtTen()
    {
//        $this->assertEquals($this->tomorrowAtTen, $this->nextAvailableTimeSlotForScheduleWithDuration());
        //if now is saturday, set schedule +2 days to next monday
        if ((new \DateTimeImmutable())->format('w') == 6) {
            $this->assertEquals((new \DateTimeImmutable('+2 days'))->setTime(10, 0),
                    $this->nextAvailableTimeSlotForScheduleWithDuration());
        } elseif ((new \DateTimeImmutable ())->format('w') == 5) {// if friday set +3 days to monday
            $this->assertEquals((new \DateTimeImmutable('+3 days'))->setTime(10, 0),
                    $this->nextAvailableTimeSlotForScheduleWithDuration());
        } else { // otherwise, set schedule for next day
            $this->assertEquals((new \DateTimeImmutable('+1 days'))->setTime(10, 0),
                    $this->nextAvailableTimeSlotForScheduleWithDuration());
        }
    }
//    public function test_nextAvailableTimeSlotForScheduleWithDuration_atFriday_returnNextAvailableTimeAtMonday()
//    {
//        if ((new \DateTimeImmutable())->format('w') == 5) {
//            $this->assertEquals((new \DateTimeImmutable('+3 days'))->setTime(10, 0),
//                    $this->nextAvailableTimeSlotForScheduleWithDuration());
//        } 
//    }
//    public function test_nextAvailableTimeSlotForScheduleWithDuration_atSaturday_returnNextAvailableTimeAtMonday()
//    {
//        if ((new \DateTimeImmutable())->format('w') == 6) {
//            $this->assertEquals((new \DateTimeImmutable('+2 days'))->setTime(10, 0),
//                    $this->nextAvailableTimeSlotForScheduleWithDuration());
//        } 
//    }
    public function test_nextAvailableTimeSlotForScheduleWithDuration_noAvailableSlotForNextDayAtTen_tryNextHour()
    {
        $this->scheduler->occupiedDurationList[$this->tomorrowAtTen->format('Y-m-d H')] = 58;
        if ((new \DateTimeImmutable())->format('w') != 5 && (new \DateTimeImmutable())->format('w') != 6 ) {
            $this->assertEquals((new \DateTimeImmutable('+1 days'))->setTime(11, 0),
                    $this->nextAvailableTimeSlotForScheduleWithDuration());
        } 
    }
    public function test_nextAvailableTimeSlotForScheduleWithDuration_noAvailableSlotForTomorrow_tryNextDay()
    {
        $this->schedule->expects($this->any())->method('getActivityDuration')->willReturn(60);
        $tomorrow = (new DateTimeImmutable('tomorrow'));
        $this->scheduler->add($tomorrow->setTime(10, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(11, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(12, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(13, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(14, 0), $this->schedule);
        $this->scheduler->add($tomorrow->setTime(15, 0), $this->schedule);
        
        //on thursday when no available time slot in friday, seek time in monday
        if ((new \DateTimeImmutable())->format('w') == 4) {
            $this->assertEquals((new \DateTimeImmutable('+4 days'))->setTime(10, 0),
                    $this->nextAvailableTimeSlotForScheduleWithDuration());
        } elseif ((new \DateTimeImmutable())->format('w') != 5 || (new \DateTimeImmutable())->format('w') != 6) {
            $this->assertEquals((new \DateTimeImmutable('+2 days'))->setTime(10, 0),
                    $this->nextAvailableTimeSlotForScheduleWithDuration());
        } else {
            $this->assertEquals((new \DateTimeImmutable('next monday'))->setTime(10, 0),
                    $this->nextAvailableTimeSlotForScheduleWithDuration());
        }
    }
    
    //
    protected function releaseRequiredDurationInTimeSlotOrDie()
    {
        $this->schedule->expects($this->any())->method('getActivityDuration')->willReturn(60);
        $this->scheduleOne->expects($this->any())->method('getActivityDuration')->willReturn(30);
        $this->scheduleTwo->expects($this->any())->method('getActivityDuration')->willReturn(15);
        $this->scheduleThree->expects($this->any())->method('getActivityDuration')->willReturn(45);
        
        $this->scheduler->add($this->tomorrow->setTime(10, 0), $this->scheduleOne);
        $this->scheduler->add($this->tomorrow->setTime(10, 0), $this->scheduleTwo);
        $this->scheduler->add($this->tomorrow->setTime(11, 0), $this->schedule);
        $this->scheduler->add($this->tomorrow->setTime(12, 0), $this->scheduleThree);
        $this->scheduler->releaseRequiredDurationInTimeSlotOrDie($this->tomorrow->setTime(10, 0), $this->requiredDuration);
    }
    public function test_releaseRequiredDurationInTimeSlotOrDie_notEnoughtSlot_forbidden()
    {
        $this->assertRegularExceptionThrowed(fn() => $this->releaseRequiredDurationInTimeSlotOrDie(), 'Forbidden', 'not enough duration available for activity');
    }
    public function test_releaseRequiredDurationInTimeSlotOrDie_enoughtSlot_void()
    {
        $this->requiredDuration = 15;
        $this->releaseRequiredDurationInTimeSlotOrDie();
        $this->markAsSuccess();
    }
    public function test_releaseRequiredDurationInTimeSlotOrDie_relocateConflictedScheduleIfInitial()
    {
        $this->scheduleTwo->expects($this->once())
                ->method('isRelocateable')
                ->willReturn(true);
        $this->scheduleTwo->expects($this->once())
                ->method('relocateTo')
                ->with($this->tomorrow->setTime(12, 0));
        $this->releaseRequiredDurationInTimeSlotOrDie();
    }
    public function test_releaseRequiredDurationInTimeSlotOrDie_changeOccupiedDurationListState()
    {
        $this->scheduleTwo->expects($this->once())
                ->method('isRelocateable')
                ->willReturn(true);
        $this->releaseRequiredDurationInTimeSlotOrDie();
        $this->assertEquals(60, $this->scheduler->occupiedDurationList[$this->tomorrow->setTime(12, 0)->format('Y-m-d H')]);
        
    }
    public function test_releaseRequiredDurationInTimeSlotOrDie_changeScheduleListState()
    {
        $this->scheduleTwo->expects($this->once())
                ->method('isRelocateable')
                ->willReturn(true);
        $this->releaseRequiredDurationInTimeSlotOrDie();
        $this->assertTrue($this->scheduler->scheduleList[$this->tomorrow->setTime(12, 0)->format('Y-m-d H')]->contains($this->scheduleTwo));
        $this->assertFalse($this->scheduler->scheduleList[$this->tomorrowAtTen->format('Y-m-d H')]->contains($this->scheduleTwo));
        
    }
    public function test_releaseRequiredDurationInTimeSlotOrDie_releasedDurationNotEnoughToFulfillRequiredDuration()
    {
        $this->requiredDuration = 60;
        $this->scheduleTwo->expects($this->once())
                ->method('isRelocateable')
                ->willReturn(true);
        $this->assertRegularExceptionThrowed(fn() => $this->releaseRequiredDurationInTimeSlotOrDie(), 'Forbidden', 'not enough duration available for activity');
    }
    public function test_releaseRequiredDurationInTimeSlotOrDie_relocateMoreThanOneSchedule()
    {
        $this->requiredDuration = 60;
        $this->scheduleTwo->expects($this->once())
                ->method('isRelocateable')
                ->willReturn(true);
        $this->scheduleOne->expects($this->once())
                ->method('isRelocateable')
                ->willReturn(true);
        $this->scheduleOne->expects($this->once())
                ->method('relocateTo')
                ->with($this->tomorrow->setTime(13, 0));
        $this->scheduleTwo->expects($this->once())
                ->method('relocateTo')
                ->with($this->tomorrow->setTime(12, 0));
        $this->releaseRequiredDurationInTimeSlotOrDie();
    }
    public function test_releaseRequiredDurationInTimeSlotOrDie_nextHourOnlyFitToReceiveOneRelocation()
    {
        $this->requiredDuration = 60;
        $this->schedule->expects($this->once())->method('getActivityDuration')->willReturn(30);
        
        $this->scheduleOne->expects($this->once())
                ->method('isRelocateable')
                ->willReturn(true);
        $this->scheduleTwo->expects($this->once())
                ->method('isRelocateable')
                ->willReturn(true);
        $this->scheduleOne->expects($this->once())
                ->method('relocateTo')
                ->with($this->tomorrow->setTime(11, 0));
        $this->scheduleTwo->expects($this->once())
                ->method('relocateTo')
                ->with($this->tomorrow->setTime(12, 0));
        $this->releaseRequiredDurationInTimeSlotOrDie();
    }
}

class TestableSalesActivitySchedulerService extends SalesActivitySchedulerService
{

    public array $scheduleList;
    public array $occupiedDurationList;
}
