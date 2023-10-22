<?php

namespace Resources;

use Illuminate\Support\Str;

class Uuid
{

    public static function generateUuid4(): string
    {
        return (string) Str::orderedUuid();
    }
}
