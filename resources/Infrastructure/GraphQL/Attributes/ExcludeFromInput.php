<?php

declare(strict_types=1);

namespace Resources\Infrastructure\GraphQL\Attributes;

use Attribute;

#[Attribute]
final class ExcludeFromInput
{
    //these entity properties automatically excluded from input field without necessity for this attribute:
    // "createdTime", "lastModifiedTime", "submitTime", "registrationTime", "disabled", "cancelled", "suspended", "removed"
}
