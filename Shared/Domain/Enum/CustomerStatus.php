<?php

namespace Shared\Domain\Enum;

enum CustomerStatus: string
{
    case NEW = 'NEW';
    case GREETING_REQUIRED = 'GREETING_REQUIRED';
    case INVALID = 'INVALID';
    case FACT_FINDING_REQUIRED = 'FACT_FINDING_REQUIRED';
    case NEGOTIATION_REQUIRED = 'NEGOTIATION_REQUIRED';
    case GOOD_FUND = 'GOOD_FUND';
}
