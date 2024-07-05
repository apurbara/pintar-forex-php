<?php

namespace Sales\Domain\Task\SalesActivitySchedule;

readonly class ViewAllNonInitialSchedulesInMonthPayload
{

    public array $result;
    public int $year;
    public int $month;

    public function setResult(array $result)
    {
        $this->result = $result;
        return $this;
    }

    public function setYear(int $year)
    {
        $this->year = $year;
        return $this;
    }

    public function setMonth(int $month)
    {
        $this->month = $month;
        return $this;
    }


}
