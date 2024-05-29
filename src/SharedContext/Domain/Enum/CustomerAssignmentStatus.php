<?php

namespace SharedContext\Domain\Enum;

enum CustomerAssignmentStatus: string
{
    case ACTIVE = "ACTIVE";
    case RECYCLED = "RECYCLED";
    case GOOD_FUND = "GOOD_FUND";
    case CANCELLED = "CANCELLED";
}
