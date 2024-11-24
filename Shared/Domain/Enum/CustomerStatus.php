<?php

namespace Shared\Domain\Enum;

enum CustomerStatus: string
{
    case NEW = 'NEW';
    case RECYCLED = 'RECYCLED';
    case FACT_FINDING_REQUIRED = 'FACT_FINDING_REQUIRED';
    case STRIKING_REQUIRED = 'STRIKING_REQUIRED';
    case GOOD_FUND = 'GOOD_FUND';
}
