<?php

namespace Shared\Domain\Enum;

enum SalesActivityScheduleStatus: string
{

    case SCHEDULED = 'SCHEDULED';
    case CANCELLED = 'CANCELLED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED_BY_SYSTEM = 'CANCELLED_BY_SYSTEM';
}
