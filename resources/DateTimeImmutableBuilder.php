<?php

namespace Resources;

use DateTime;
use DateTimeImmutable;

class DateTimeImmutableBuilder
{
    public static function buildYmdHisAccuracy(string $time = 'now'): DateTimeImmutable
    {
        $timeInYmdHis = (new DateTime($time))->format('Y-m-d H:i:s');
        return new DateTimeImmutable($timeInYmdHis);
    }
}
