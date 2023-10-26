<?php

namespace SharedContext\Domain\Enum;

enum ScheduledSalesActivityStatus: string
{

    case SCHEDULED = 'SCHEDULED';
    case CANCELLED = 'CANCELLED';
    case COMPLETED = 'COMPLETED';
}
