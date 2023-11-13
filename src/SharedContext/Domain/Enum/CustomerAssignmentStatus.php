<?php

namespace SharedContext\Domain\Enum;

enum CustomerAssignmentStatus: string
{
    case ACTIVE = "ACTIVE";
    case RECYCLED = "RECYCLED";
    case CLOSED = "CLOSED";
}
