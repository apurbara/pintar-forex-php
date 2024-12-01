<?php

namespace Shared\Domain\Enum;

enum CustomerAssignmentStatus: string
{
    case ACTIVE = "ACTIVE";
    case COMPLETED = "COMPLETED";
    case RECYCLED = "RECYCLED";
    case CANCELLED = "CANCELLED";
    case CANCELLED_BY_SYSTEM = "CANCELLED_BY_SYSTEM";
}
