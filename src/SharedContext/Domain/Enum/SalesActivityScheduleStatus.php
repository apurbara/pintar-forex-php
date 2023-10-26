<?php

namespace SharedContext\Domain\Enum;

enum SalesActivityScheduleStatus: string
{

    case SCHEDULED = 'SCHEDULED';
    case CANCELLED = 'CANCELLED';
    case COMPLETED = 'COMPLETED';
}
