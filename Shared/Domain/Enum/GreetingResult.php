<?php

namespace Shared\Domain\Enum;

enum GreetingResult: string
{
    case VALIDATED = 'VALIDATED';
    case RECYCLED = 'RECYCLED';
}
