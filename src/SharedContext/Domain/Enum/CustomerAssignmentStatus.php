<?php

namespace SharedContext\Domain\Enum;

enum CustomerAssignmentStatus: string
{
    case ACTIVE = "ACTIVE";
    case RECYCLED = "RECYCLED";
//    case CLOSED = "CLOSED";
    case GOOD_FUND = "GOOD_FUND";
}
