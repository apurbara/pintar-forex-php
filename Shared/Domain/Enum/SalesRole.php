<?php

namespace Shared\Domain\Enum;

enum SalesRole: string
{
    case GREETER = 'GREETER';
    case FACT_FINDER = 'FACT_FINDER';
    case STRIKER = 'STRIKER';
}
