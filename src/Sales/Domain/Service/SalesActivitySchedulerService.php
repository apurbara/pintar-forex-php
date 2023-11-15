<?php

namespace Sales\Domain\Service;

use DateInterval;
use DateTimeImmutable;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\SalesActivity;

class SalesActivitySchedulerService
{

    protected array $scheduleList;

    public function __construct()
    {
        $this->scheduleList = [];
    }

    public function add(DateTimeImmutable $startTime, SalesActivitySchedule $schedule): void
    {
        $this->scheduleList[$startTime->format('Y-m-d H')][] = $schedule;
    }

    public function nextAvailableStartTimeForInitialSalesActivity(SalesActivity $salesActivity): DateTimeImmutable
    {
        $nextNearestSchedule = $this->nextNearestSchedule();
        while (true) {
            $filledDuration = 0;
            foreach ($this->scheduleList[$nextNearestSchedule->format('Y-m-d H')] ?? [] as $activitySchedule) {
                $filledDuration += $activitySchedule->getActivityDuration();
            }
            if ((60 - $filledDuration) >= $salesActivity->getDuration()) {
                return $nextNearestSchedule;
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
