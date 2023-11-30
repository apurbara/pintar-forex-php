<?php

namespace Sales\Domain\Service;

use DateInterval;
use DateTimeImmutable;
use Resources\Exception\RegularException;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use SplObjectStorage;

class SalesActivitySchedulerService
{

    protected array $scheduleList;
    protected array $occupiedDurationList;

    public function __construct()
    {
        $this->scheduleList = [];
        $this->occupiedDurationList = [];
    }

    public function add(DateTimeImmutable $startTime, SalesActivitySchedule $schedule): void
    {
        $hourIndex = $startTime->format('Y-m-d H');
        if (!isset($this->scheduleList[$hourIndex])) {
            $this->scheduleList[$hourIndex] = new SplObjectStorage();
        }
        $this->scheduleList[$hourIndex]->attach($schedule);
        $this->occupiedDurationList[$hourIndex] = ($this->occupiedDurationList[$hourIndex] ?? 0) + $schedule->getActivityDuration();
    }

    public function nextAvailableTimeSlotForScheduleWithDuration(int $requiredDuration): DateTimeImmutable
    {
        $nextNearestSchedule = $this->nextNearestSchedule();
        while (true) {
            if (60 - ($this->occupiedDurationList[$nextNearestSchedule->format('Y-m-d H')] ?? 0) >= $requiredDuration) {
                return $nextNearestSchedule;
            }
            $nextNearestSchedule = $this->nextHourSchedule($nextNearestSchedule);
        }
    }

    public function releaseRequiredDurationInTimeSlotOrDie(DateTimeImmutable $startTime, int $requiredDuration): void
    {
        $indexHour = $startTime->format('Y-m-d H');
        $totalDurationToRelease = $requiredDuration - (60 - ($this->occupiedDurationList[$indexHour] ?? 0));
        if ($totalDurationToRelease <= 0) {
            return;
        }
        foreach ($this->scheduleList[$indexHour] ?? new SplObjectStorage() as $toRelocateSchedule) {
            $relocatedScheduleList = [];
            if ($totalDurationToRelease > 0) {
                $relocatedDuration = $this->relocateInitialScheduleToNextAvailableTime($toRelocateSchedule, $startTime);
                if ($relocatedDuration) {
                    $totalDurationToRelease -= $relocatedDuration;
                    $relocatedScheduleList[] = $toRelocateSchedule;
                }
            }
        }

        if ($totalDurationToRelease > 0) {
            throw RegularException::forbidden('not enough duration available for activity');
        }
        foreach ($relocatedScheduleList as $schedule) {
            $this->scheduleList[$indexHour]->detach($schedule);
        }
    }

    /**
     * 
     * @param SalesActivitySchedule $toRelocateSchedule
     * @return int duration slot successfully released
     */
    protected function relocateInitialScheduleToNextAvailableTime(
            SalesActivitySchedule $toRelocateSchedule, DateTimeImmutable $currentlyOccupiedStartTime): int
    {
        $toRelocateDuration = $toRelocateSchedule->getActivityDuration();
        if (!$toRelocateSchedule->isRelocateable()) {
            return 0;
        }
        $nextNearestSchedule = $this->nextHourSchedule($currentlyOccupiedStartTime);
        while (true) {
            $indexHour = $nextNearestSchedule->format('Y-m-d H');
            if (60 - ($this->occupiedDurationList[$indexHour] ?? 0) >= $toRelocateDuration) {
                $toRelocateSchedule->relocateTo($nextNearestSchedule);
                $this->occupiedDurationList[$indexHour] = ($this->occupiedDurationList[$indexHour] ?? 0) + $toRelocateDuration;
                if (!isset($this->scheduleList[$indexHour])) {
                    $this->scheduleList[$indexHour] = new SplObjectStorage();
                }
                $this->scheduleList[$indexHour]->attach($toRelocateSchedule);
                return $toRelocateDuration;
            }
            $nextNearestSchedule = $this->nextHourSchedule($nextNearestSchedule);
        }
    }

    //
    protected function nextNearestSchedule(): DateTimeImmutable
    {
        $currentTime = new DateTimeImmutable();
        if ($currentTime->format('w') == 5) { // today is friday
            return $currentTime->add(new DateInterval('P3D'))->setTime(10, 0);
        } elseif ($currentTime->format('w') == 6) { // today is saturday
            return $currentTime->add(new DateInterval('P2D'))->setTime(10, 0);
        }
        return $currentTime->add(new DateInterval('P1D'))->setTime(10, 0);
    }

    protected function nextHourSchedule(DateTimeImmutable $baseDateTime): DateTimeImmutable
    {
        //for relocating it should be safe assuming there will never be initial activity in non weekday at 10:00 to 15:00
        if ($baseDateTime->format('H') < 15) { // at tomorrow before 16:00
            return $baseDateTime->add(new \DateInterval('PT1H'));
        }
        if ($baseDateTime->format('w') == 5) { // after 16:00 when tomorrow is friday
            return $baseDateTime->add(new DateInterval('P3D'))->setTime(10, 0);
        } elseif ($baseDateTime->format('w') == 6) { // after 16:00 when tomorrow is saturday -- should never occured :D
            return $baseDateTime->add(new DateInterval('P2D'))->setTime(10, 0);
        }
        return $baseDateTime->add(new DateInterval('P1D'))->setTime(10, 0);
    }
}
